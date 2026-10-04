<?php

namespace App\Http\Controllers;

use App\Models\ExternalWorkforceAttendance;
use App\Models\ExternalWorkforceWorker;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExternalWorkforceAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage employees');

        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to = $request->input('date_to', now()->toDateString());

        $query = ExternalWorkforceAttendance::with(['worker', 'vendor', 'contract', 'company', 'branch', 'costCenter'])
            ->whereBetween('attendance_date', [$from, $to])
            ->latest('attendance_date')
            ->latest();

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('handoff_status')) {
            $query->where('handoff_status', $request->handoff_status);
        }

        $attendances = $query->limit(180)->get();

        return view('external-workforce.attendance', [
            'active' => 'external-workforce-attendance',
            'workers' => ExternalWorkforceWorker::with(['vendor', 'contract'])->whereIn('status', ['active', 'candidate'])->orderBy('full_name')->get(),
            'attendances' => $attendances,
            'filters' => [
                'date_from' => $from,
                'date_to' => $to,
                'approval_status' => $request->input('approval_status', ''),
                'handoff_status' => $request->input('handoff_status', ''),
            ],
            'summary' => [
                'total' => $attendances->count(),
                'approved' => $attendances->where('approval_status', 'approved')->count(),
                'mandays' => $attendances->sum(fn (ExternalWorkforceAttendance $attendance) => (float) $attendance->mandays),
                'ready_handoff' => $attendances->where('handoff_status', 'ready')->count(),
                'gross_amount' => $attendances->sum(fn (ExternalWorkforceAttendance $attendance) => (float) $attendance->gross_amount),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'external_workforce_worker_id' => ['required', 'exists:external_workforce_workers,id'],
            'attendance_number' => ['required', 'string', 'max:80', 'unique:external_workforce_attendances,attendance_number'],
            'attendance_date' => ['required', 'date'],
            'shift_code' => ['nullable', 'string', 'max:80'],
            'clock_in_at' => ['nullable', 'date'],
            'clock_out_at' => ['nullable', 'date', 'after_or_equal:clock_in_at'],
            'attendance_status' => ['required', Rule::in(['present', 'absent', 'half_day', 'leave', 'sick', 'holiday', 'no_show'])],
            'mandays' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'overtime_rate' => ['nullable', 'numeric', 'min:0'],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected'])],
            'approved_by' => ['nullable', 'string', 'max:160'],
            'source_type' => ['required', Rule::in(['manual', 'agent', 'csv', 'vendor_report'])],
            'evidence_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $worker = ExternalWorkforceWorker::with(['contract', 'vendor'])->findOrFail($data['external_workforce_worker_id']);

        $mandays = $this->resolveMandays($data['attendance_status'], (float) ($data['mandays'] ?? 0));
        $dailyRate = ((float) ($data['daily_rate'] ?? 0)) > 0 ? (float) $data['daily_rate'] : (float) $worker->daily_rate;
        $overtimeRate = ((float) ($data['overtime_rate'] ?? 0)) > 0 ? (float) $data['overtime_rate'] : (float) ($worker->contract?->overtime_rate ?? 0);
        $overtimeHours = (float) ($data['overtime_hours'] ?? 0);
        $grossAmount = ($mandays * $dailyRate) + ($overtimeHours * $overtimeRate);
        $approvedAt = $data['approval_status'] === 'approved' ? Carbon::now() : null;

        $attendance = ExternalWorkforceAttendance::create([
            ...$data,
            'external_workforce_vendor_id' => $worker->external_workforce_vendor_id,
            'external_workforce_contract_id' => $worker->external_workforce_contract_id,
            'company_id' => $worker->company_id,
            'branch_id' => $worker->branch_id,
            'cost_center_id' => $worker->cost_center_id,
            'mandays' => $mandays,
            'daily_rate' => $dailyRate,
            'overtime_rate' => $overtimeRate,
            'overtime_hours' => $overtimeHours,
            'gross_amount' => $grossAmount,
            'approved_at' => $approvedAt,
            'handoff_target' => $worker->payment_handling,
            'handoff_status' => $this->handoffStatus($worker->payment_handling, $data['approval_status']),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'external_workforce_attendance.created',
            "Attendance external workforce {$attendance->attendance_number} dibuat.",
            $attendance,
            [],
            $attendance->toArray(),
            $request,
        );

        return redirect()->route('external-workforce.attendance')->with('success', 'Attendance & mandays berhasil disimpan.');
    }

    public function review(Request $request, ExternalWorkforceAttendance $attendance): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'approved_by' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $before = $attendance->toArray();

        $attendance->update([
            'approval_status' => $data['decision'],
            'approved_by' => $data['approved_by'],
            'approved_at' => Carbon::now(),
            'handoff_status' => $this->handoffStatus($attendance->handoff_target, $data['decision']),
            'notes' => trim(($attendance->notes ? $attendance->notes."\n" : '').($data['notes'] ?? '')),
        ]);

        AuditLogger::record(
            'external_workforce_attendance.reviewed',
            "Attendance external workforce {$attendance->attendance_number} direview.",
            $attendance,
            $before,
            $attendance->fresh()->toArray(),
            $request,
        );

        return redirect()->route('external-workforce.attendance')->with('success', 'Approval attendance berhasil diperbarui.');
    }

    private function resolveMandays(string $status, float $input): float
    {
        return match ($status) {
            'present' => $input > 0 ? $input : 1.0,
            'half_day' => $input > 0 ? min($input, 0.5) : 0.5,
            'absent', 'no_show' => 0.0,
            default => $input,
        };
    }

    private function handoffStatus(string $target, string $approvalStatus): string
    {
        if ($target === 'operational_only') {
            return 'not_required';
        }

        return $approvalStatus === 'approved' ? 'ready' : 'pending';
    }
}
