<?php

namespace App\Http\Controllers;

use App\Models\AttendanceClockEvent;
use App\Models\AttendanceDeviceBinding;
use App\Models\AttendanceShiftAssignment;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceClockController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view attendance');

        $events = AttendanceClockEvent::with(['employeeProfile', 'deviceBinding', 'shiftAssignment.shiftPattern'])
            ->latest('event_at')
            ->limit(150)
            ->get();

        return view('attendance.clock-events', [
            'active' => 'attendance-clock',
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'shiftAssignments' => AttendanceShiftAssignment::with(['shiftPattern', 'employeeProfile'])->latest()->limit(120)->get(),
            'events' => $events,
            'devices' => AttendanceDeviceBinding::with('employeeProfile')->latest('last_seen_at')->limit(100)->get(),
            'summary' => [
                'total' => $events->count(),
                'approved' => $events->where('status', 'approved')->count(),
                'need_review' => $events->where('status', 'need_review')->count(),
                'trusted_device' => $events->where('is_device_trusted', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('approve attendance');

        $validated = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'attendance_shift_assignment_id' => ['nullable', 'exists:attendance_shift_assignments,id'],
            'event_number' => ['required', 'string', 'max:80', 'unique:attendance_clock_events,event_number'],
            'event_type' => ['required', Rule::in(['clock_in', 'clock_out', 'break_start', 'break_end'])],
            'event_at' => ['required', 'date'],
            'source' => ['required', Rule::in(['mobile_webview', 'admin_manual', 'biometric_import'])],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_accuracy_meter' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'location_label' => ['nullable', 'string', 'max:180'],
            'selfie_reference' => ['nullable', 'string', 'max:255'],
            'liveness_score' => ['required', 'integer', 'min:0', 'max:100'],
            'device_uid' => ['nullable', 'string', 'max:160'],
            'device_name' => ['nullable', 'string', 'max:160'],
            'platform' => ['nullable', 'string', 'max:40'],
            'app_version' => ['nullable', 'string', 'max:40'],
            'bind_device' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['submitted', 'approved', 'need_review', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $deviceBinding = null;
        if (! empty($validated['device_uid'])) {
            $deviceBinding = AttendanceDeviceBinding::firstOrCreate(
                [
                    'employee_profile_id' => $validated['employee_profile_id'],
                    'device_uid' => $validated['device_uid'],
                ],
                [
                    'device_name' => $validated['device_name'] ?? null,
                    'platform' => $validated['platform'] ?? 'webview',
                    'app_version' => $validated['app_version'] ?? null,
                    'bound_at' => $request->boolean('bind_device') ? now() : null,
                    'status' => $request->boolean('bind_device') ? 'active' : 'pending',
                ],
            );

            $deviceBinding->forceFill([
                'device_name' => $validated['device_name'] ?? $deviceBinding->device_name,
                'platform' => $validated['platform'] ?? $deviceBinding->platform,
                'app_version' => $validated['app_version'] ?? $deviceBinding->app_version,
                'bound_at' => $deviceBinding->bound_at ?: ($request->boolean('bind_device') ? now() : null),
                'last_seen_at' => now(),
                'status' => $request->boolean('bind_device') ? 'active' : $deviceBinding->status,
            ])->save();
        }

        $riskFlags = $this->riskFlags($validated, $deviceBinding);
        $isLivenessPassed = (int) $validated['liveness_score'] >= 75 && ! empty($validated['selfie_reference']);
        $isDeviceTrusted = $deviceBinding?->status === 'active';

        if ($riskFlags !== [] && $validated['status'] === 'approved') {
            $validated['status'] = 'need_review';
        }

        $clockEvent = AttendanceClockEvent::create([
            ...collect($validated)->except(['platform', 'app_version', 'bind_device'])->all(),
            'attendance_device_binding_id' => $deviceBinding?->id,
            'is_liveness_passed' => $isLivenessPassed,
            'is_device_trusted' => $isDeviceTrusted,
            'risk_flags' => $riskFlags,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'attendance_clock_event.created',
            "Clock event {$clockEvent->event_number} untuk {$clockEvent->employeeProfile?->employee_number} dibuat.",
            $clockEvent,
            [],
            $clockEvent->toArray(),
            $request,
        );

        return redirect()->route('attendance.clock-events')->with('success', 'Clock event berhasil dicatat.');
    }

    private function riskFlags(array $data, ?AttendanceDeviceBinding $deviceBinding): array
    {
        $flags = [];

        if (empty($data['latitude']) || empty($data['longitude'])) {
            $flags[] = 'gps_missing';
        }

        if (! empty($data['gps_accuracy_meter']) && (int) $data['gps_accuracy_meter'] > 100) {
            $flags[] = 'gps_low_accuracy';
        }

        if (empty($data['selfie_reference'])) {
            $flags[] = 'selfie_missing';
        }

        if ((int) $data['liveness_score'] < 75) {
            $flags[] = 'liveness_low_score';
        }

        if (! empty($data['device_uid']) && $deviceBinding?->status !== 'active') {
            $flags[] = 'device_not_trusted';
        }

        return $flags;
    }
}
