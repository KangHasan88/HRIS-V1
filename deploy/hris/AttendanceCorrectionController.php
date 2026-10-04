<?php

namespace App\Http\Controllers;

use App\Models\AttendanceClockEvent;
use App\Models\AttendanceCorrection;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceCorrectionController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view attendance');

        $corrections = AttendanceCorrection::with(['employeeProfile', 'clockEvent'])->latest()->limit(120)->get();

        return view('attendance.corrections', [
            'active' => 'attendance-corrections',
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'clockEvents' => AttendanceClockEvent::with('employeeProfile')->latest('event_at')->limit(120)->get(),
            'corrections' => $corrections,
            'summary' => [
                'total' => $corrections->count(),
                'submitted' => $corrections->where('approval_status', 'submitted')->count(),
                'approved' => $corrections->where('approval_status', 'approved')->count(),
                'generated' => $corrections->where('creates_clock_event', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('approve attendance');

        $validated = $request->validate([
            'correction_number' => ['required', 'string', 'max:80', 'unique:attendance_corrections,correction_number'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'attendance_clock_event_id' => ['nullable', 'exists:attendance_clock_events,id'],
            'attendance_date' => ['required', 'date'],
            'correction_type' => ['required', Rule::in(['missing_clock_in', 'missing_clock_out', 'wrong_time', 'wrong_type', 'duplicate_punch'])],
            'original_event_at' => ['nullable', 'date'],
            'requested_event_at' => ['required', 'date'],
            'requested_event_type' => ['required', Rule::in(['clock_in', 'clock_out', 'break_start', 'break_end'])],
            'request_source' => ['required', Rule::in(['web_admin', 'ess_webview', 'mobile_webview'])],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'cancelled'])],
            'current_approver' => ['nullable', 'string', 'max:255'],
            'creates_clock_event' => ['nullable', 'boolean'],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1200'],
            'decision_notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $isDecided = in_array($validated['approval_status'], ['approved', 'rejected'], true);
        $generateEvent = $request->boolean('creates_clock_event') && $validated['approval_status'] === 'approved';
        $generatedEventNumber = $generateEvent ? 'CORR-' . $validated['correction_number'] : null;

        $correction = AttendanceCorrection::create([
            ...$validated,
            'creates_clock_event' => $generateEvent,
            'generated_event_number' => $generatedEventNumber,
            'submitted_at' => in_array($validated['approval_status'], ['submitted', 'approved', 'rejected'], true) ? now() : null,
            'decided_at' => $isDecided ? now() : null,
            'decided_by' => $isDecided ? (auth()->user()?->name ?? 'System') : null,
            'correction_snapshot' => [
                'correction_type' => $validated['correction_type'],
                'original_event_at' => $validated['original_event_at'] ?? null,
                'requested_event_at' => $validated['requested_event_at'],
                'requested_event_type' => $validated['requested_event_type'],
                'generated_event_number' => $generatedEventNumber,
            ],
            'created_by' => auth()->id(),
        ]);

        if ($generateEvent) {
            AttendanceClockEvent::create([
                'employee_profile_id' => $validated['employee_profile_id'],
                'event_number' => $generatedEventNumber,
                'event_type' => $validated['requested_event_type'],
                'event_at' => $validated['requested_event_at'],
                'source' => 'admin_manual',
                'liveness_score' => 100,
                'is_liveness_passed' => true,
                'is_device_trusted' => true,
                'status' => 'approved',
                'risk_flags' => ['attendance_correction'],
                'notes' => 'Generated from correction ' . $validated['correction_number'],
                'created_by' => auth()->id(),
            ]);
        }

        AuditLogger::record(
            'attendance_correction.created',
            "Koreksi absensi {$correction->correction_number} dibuat.",
            $correction,
            [],
            $correction->toArray(),
            $request,
        );

        return redirect()->route('attendance.corrections')->with('success', 'Koreksi absensi berhasil dibuat.');
    }
}
