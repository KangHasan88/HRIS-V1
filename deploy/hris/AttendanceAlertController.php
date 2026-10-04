<?php

namespace App\Http\Controllers;

use App\Models\AttendanceAlert;
use App\Models\AttendanceClockEvent;
use App\Models\AttendanceShiftAssignment;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceAlertController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view attendance');

        $alerts = AttendanceAlert::with(['employeeProfile', 'clockEvent', 'shiftAssignment.shiftPattern'])
            ->latest('alert_date')
            ->latest()
            ->limit(150)
            ->get();

        return view('attendance.alerts', [
            'active' => 'attendance-alerts',
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'clockEvents' => AttendanceClockEvent::with('employeeProfile')->latest('event_at')->limit(120)->get(),
            'shiftAssignments' => AttendanceShiftAssignment::with(['shiftPattern', 'employeeProfile'])->latest()->limit(120)->get(),
            'alerts' => $alerts,
            'summary' => [
                'total' => $alerts->count(),
                'open' => $alerts->whereIn('status', ['open', 'in_review'])->count(),
                'critical' => $alerts->whereIn('severity', ['high', 'critical'])->count(),
                'resolved' => $alerts->where('status', 'resolved')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('approve attendance');

        $validated = $request->validate([
            'alert_number' => ['required', 'string', 'max:80', 'unique:attendance_alerts,alert_number'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'attendance_clock_event_id' => ['nullable', 'exists:attendance_clock_events,id'],
            'attendance_shift_assignment_id' => ['nullable', 'exists:attendance_shift_assignments,id'],
            'alert_date' => ['required', 'date'],
            'alert_type' => ['required', Rule::in(['late_arrival', 'absence', 'fatigue', 'early_leave', 'missing_punch', 'compliance'])],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['required', Rule::in(['open', 'in_review', 'resolved', 'ignored', 'escalated'])],
            'late_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'worked_hours' => ['nullable', 'numeric', 'min:0', 'max:48'],
            'fatigue_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'compliance_rule' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'trigger_reason' => ['nullable', 'string', 'max:1200'],
            'follow_up_notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $isResolved = in_array($validated['status'], ['resolved', 'ignored'], true);

        $alert = AttendanceAlert::create([
            ...$validated,
            'late_minutes' => $validated['late_minutes'] ?? 0,
            'worked_hours' => $validated['worked_hours'] ?? 0,
            'fatigue_score' => $validated['fatigue_score'] ?? 0,
            'resolved_at' => $isResolved ? now() : null,
            'resolved_by' => $isResolved ? (auth()->user()?->name ?? 'System') : null,
            'alert_snapshot' => [
                'alert_type' => $validated['alert_type'],
                'severity' => $validated['severity'],
                'status' => $validated['status'],
                'late_minutes' => (int) ($validated['late_minutes'] ?? 0),
                'worked_hours' => (float) ($validated['worked_hours'] ?? 0),
                'fatigue_score' => (float) ($validated['fatigue_score'] ?? 0),
                'compliance_rule' => $validated['compliance_rule'] ?? null,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'attendance_alert.created',
            "Alert absensi {$alert->alert_number} dibuat.",
            $alert,
            [],
            $alert->toArray(),
            $request,
        );

        return redirect()->route('attendance.alerts')->with('success', 'Alert absensi berhasil dibuat.');
    }
}
