<?php

namespace App\Http\Controllers;

use App\Models\AttendanceAnomalyDetection;
use App\Models\AttendanceClockEvent;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AttendanceAnomalyController extends Controller
{
    public function index(): View
    {
        $this->authorizeAnalytics();

        $detections = AttendanceAnomalyDetection::with(['employeeProfile.branch', 'clockEvent'])
            ->latest('detected_at')
            ->limit(160)
            ->get();

        return view('analytics.attendance-anomalies', [
            'active' => 'attendance-anomalies',
            'detections' => $detections,
            'summary' => [
                'total' => $detections->count(),
                'open' => $detections->where('status', 'open')->count(),
                'high' => $detections->whereIn('severity', ['high', 'critical'])->count(),
                'ghost' => $detections->where('anomaly_type', 'ghost_punch')->count(),
            ],
        ]);
    }

    public function scan(Request $request): RedirectResponse
    {
        $this->authorizeAnalytics();

        $validated = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'late_after_time' => ['required', 'date_format:H:i'],
            'min_repeated_late' => ['required', 'integer', 'min:2', 'max:30'],
        ]);

        $periodStart = Carbon::parse($validated['period_start'])->startOfDay();
        $periodEnd = Carbon::parse($validated['period_end'])->endOfDay();
        $lateAfter = $validated['late_after_time'];

        $events = AttendanceClockEvent::with(['employeeProfile.branch', 'deviceBinding'])
            ->whereBetween('event_at', [$periodStart, $periodEnd])
            ->orderBy('event_at')
            ->get();

        $createdOrUpdated = collect();
        foreach ($events as $event) {
            $riskFlags = $event->risk_flags ?? [];
            if ($riskFlags !== []) {
                $type = collect($riskFlags)->contains(fn ($flag) => in_array($flag, ['selfie_missing', 'liveness_low_score', 'device_not_trusted'], true))
                    ? 'ghost_punch'
                    : 'fraud_risk';

                $createdOrUpdated->push($this->storeDetection($event, $type, $periodStart, $periodEnd, [
                    'risk_flags' => $riskFlags,
                    'reason' => 'Clock event memiliki risk flags dari mobile attendance.',
                ]));
            }

            if ($event->event_type === 'clock_in' && $event->event_at?->format('H:i') > $lateAfter) {
                $createdOrUpdated->push($this->storeDetection($event, 'late_arrival', $periodStart, $periodEnd, [
                    'late_after_time' => $lateAfter,
                    'clock_time' => $event->event_at?->format('H:i'),
                    'reason' => 'Clock-in melewati batas waktu scan.',
                ]));
            }
        }

        $lateGroups = $events
            ->filter(fn (AttendanceClockEvent $event) => $event->event_type === 'clock_in' && $event->event_at?->format('H:i') > $lateAfter)
            ->groupBy('employee_profile_id')
            ->filter(fn (Collection $items) => $items->count() >= (int) $validated['min_repeated_late']);

        foreach ($lateGroups as $employeeId => $lateEvents) {
            $firstEvent = $lateEvents->first();
            $createdOrUpdated->push($this->storeDetection($firstEvent, 'repeated_late', $periodStart, $periodEnd, [
                'late_count' => $lateEvents->count(),
                'event_numbers' => $lateEvents->pluck('event_number')->values(),
                'late_after_time' => $lateAfter,
                'reason' => 'Karyawan terlambat berulang dalam periode scan.',
            ], (int) $employeeId));
        }

        return redirect()
            ->route('analytics.attendance-anomalies')
            ->with('success', $createdOrUpdated->unique('id')->count() . ' anomali absensi berhasil diproses.');
    }

    private function storeDetection(AttendanceClockEvent $event, string $type, Carbon $periodStart, Carbon $periodEnd, array $evidence, ?int $employeeId = null): AttendanceAnomalyDetection
    {
        $employeeId ??= $event->employee_profile_id;
        $key = implode(':', [
            $type,
            $employeeId,
            $type === 'repeated_late' ? $periodStart->toDateString() . '_' . $periodEnd->toDateString() : $event->id,
        ]);

        $severity = match ($type) {
            'ghost_punch' => 'critical',
            'fraud_risk', 'repeated_late' => 'high',
            default => 'medium',
        };

        $score = match ($type) {
            'ghost_punch' => 95,
            'fraud_risk' => 85,
            'repeated_late' => 78,
            default => 65,
        };

        $detection = AttendanceAnomalyDetection::updateOrCreate(
            ['detection_key' => $key],
            [
                'employee_profile_id' => $employeeId,
                'attendance_clock_event_id' => $event->id,
                'detection_number' => 'ANM-' . strtoupper(substr(sha1($key), 0, 12)),
                'anomaly_type' => $type,
                'severity' => $severity,
                'detected_at' => now(),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'score' => $score,
                'status' => 'open',
                'evidence_snapshot' => [
                    ...$evidence,
                    'employee_number' => $event->employeeProfile?->employee_number,
                    'employee_name' => $event->employeeProfile?->full_name,
                    'event_number' => $event->event_number,
                    'event_at' => $event->event_at?->toDateTimeString(),
                    'source' => $event->source,
                    'latitude' => $event->latitude,
                    'longitude' => $event->longitude,
                    'selfie_reference' => $event->selfie_reference,
                    'device_uid' => $event->device_uid,
                    'liveness_score' => $event->liveness_score,
                ],
                'recommendation' => $this->recommendation($type),
                'created_by' => auth()->id(),
            ],
        );

        AuditLogger::record(
            'attendance_anomaly.detected',
            "Anomali absensi {$detection->detection_number} terdeteksi.",
            $detection,
            [],
            $detection->toArray(),
            request(),
        );

        return $detection;
    }

    private function recommendation(string $type): string
    {
        return match ($type) {
            'ghost_punch' => 'Review bukti selfie, device trust, lokasi GPS, dan minta klarifikasi atasan sebelum payroll.',
            'fraud_risk' => 'Bandingkan lokasi dengan geofence, roster, dan histori device karyawan.',
            'repeated_late' => 'Jadwalkan coaching, cek pola shift, dan monitor keterlambatan berikutnya.',
            default => 'Validasi data clock event dan buat follow-up jika berdampak ke payroll.',
        };
    }

    private function authorizeAnalytics(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance') || $user?->hasPermission('view delivery progress'), 403);
    }
}
