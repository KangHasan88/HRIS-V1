<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrection;
use App\Models\BusinessTripRequest;
use App\Models\EmployeeProfile;
use App\Models\LeavePolicyType;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EssRequestCenterController extends Controller
{
    public function index(): View
    {
        $employee = $this->currentEmployee();
        $leaveRequests = collect();
        $overtimeRequests = collect();
        $tripRequests = collect();
        $corrections = collect();

        if ($employee) {
            $leaveRequests = LeaveRequest::with('policyType')->where('employee_profile_id', $employee->id)->latest()->limit(20)->get();
            $overtimeRequests = OvertimeRequest::where('employee_profile_id', $employee->id)->latest()->limit(20)->get();
            $tripRequests = BusinessTripRequest::where('employee_profile_id', $employee->id)->latest()->limit(20)->get();
            $corrections = AttendanceCorrection::where('employee_profile_id', $employee->id)->latest()->limit(20)->get();
        }

        return view('ess.request-center', [
            'active' => 'ess-request-center',
            'employee' => $employee,
            'policyTypes' => LeavePolicyType::where('status', 'active')->orderBy('code')->get(),
            'leaveRequests' => $leaveRequests,
            'overtimeRequests' => $overtimeRequests,
            'tripRequests' => $tripRequests,
            'corrections' => $corrections,
            'summary' => [
                'leave' => $leaveRequests->count(),
                'overtime' => $overtimeRequests->count(),
                'trip' => $tripRequests->count(),
                'correction' => $corrections->count(),
            ],
        ]);
    }

    public function storeOvertime(Request $request): RedirectResponse
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return $this->missingEmployeeProfileResponse();
        }

        $validated = $request->validate([
            'request_number' => ['required', 'string', 'max:80', 'unique:overtime_requests,request_number'],
            'overtime_date' => ['required', 'date'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'overtime_type' => ['required', Rule::in(['regular_day', 'night_shift', 'rest_day', 'holiday'])],
            'request_source' => ['required', Rule::in(['ess_webview', 'mobile_webview'])],
            'reason' => ['nullable', 'string', 'max:1200'],
        ]);

        $hours = round(max(0, \Carbon\Carbon::parse($validated['start_at'])->diffInMinutes(\Carbon\Carbon::parse($validated['end_at'])) / 60), 2);
        $multiplier = match ($validated['overtime_type']) {
            'holiday' => 2.0,
            'rest_day' => 1.75,
            'night_shift' => 1.5,
            default => 1.25,
        };

        $overtime = OvertimeRequest::create([
            ...$validated,
            'employee_profile_id' => $employee->id,
            'requested_hours' => $hours,
            'approved_hours' => 0,
            'payroll_multiplier' => $multiplier,
            'hourly_rate' => 0,
            'estimated_amount' => 0,
            'approval_status' => 'submitted',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now(),
            'calculation_snapshot' => [
                'source' => 'ESS Request Center',
                'requested_hours' => $hours,
                'payroll_multiplier' => $multiplier,
                'needs_manager_approval' => true,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('ess_overtime_request.submitted', "ESS lembur {$overtime->request_number} diajukan oleh {$employee->employee_number}.", $overtime, [], $overtime->toArray(), $request);

        return redirect()->route('ess.request-center')->with('success', 'Pengajuan lembur berhasil dikirim.');
    }

    public function storeTrip(Request $request): RedirectResponse
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return $this->missingEmployeeProfileResponse();
        }

        $validated = $request->validate([
            'request_number' => ['required', 'string', 'max:80', 'unique:business_trip_requests,request_number'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'destination_city' => ['required', 'string', 'max:255'],
            'destination_site' => ['nullable', 'string', 'max:255'],
            'trip_type' => ['required', Rule::in(['domestic', 'site_visit', 'training', 'client_visit'])],
            'transport_mode' => ['nullable', 'string', 'max:80'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'request_source' => ['required', Rule::in(['ess_webview', 'mobile_webview'])],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:1200'],
        ]);

        $trip = BusinessTripRequest::create([
            ...$validated,
            'employee_profile_id' => $employee->id,
            'estimated_budget' => $validated['estimated_budget'] ?? 0,
            'approval_status' => 'submitted',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now(),
            'trip_snapshot' => [
                'duration_days' => \Carbon\Carbon::parse($validated['start_date'])->diffInDays(\Carbon\Carbon::parse($validated['end_date'])) + 1,
                'destination_city' => $validated['destination_city'],
                'trip_type' => $validated['trip_type'],
                'transport_mode' => $validated['transport_mode'] ?? null,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('ess_business_trip.submitted', "ESS dinas {$trip->request_number} diajukan oleh {$employee->employee_number}.", $trip, [], $trip->toArray(), $request);

        return redirect()->route('ess.request-center')->with('success', 'Pengajuan dinas berhasil dikirim.');
    }

    public function storeCorrection(Request $request): RedirectResponse
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return $this->missingEmployeeProfileResponse();
        }

        $validated = $request->validate([
            'correction_number' => ['required', 'string', 'max:80', 'unique:attendance_corrections,correction_number'],
            'attendance_date' => ['required', 'date'],
            'correction_type' => ['required', Rule::in(['missing_clock_in', 'missing_clock_out', 'wrong_time', 'wrong_type'])],
            'original_event_at' => ['nullable', 'date'],
            'requested_event_at' => ['required', 'date'],
            'requested_event_type' => ['required', Rule::in(['clock_in', 'clock_out', 'break_start', 'break_end'])],
            'request_source' => ['required', Rule::in(['ess_webview', 'mobile_webview'])],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1200'],
        ]);

        $correction = AttendanceCorrection::create([
            ...$validated,
            'employee_profile_id' => $employee->id,
            'approval_status' => 'submitted',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now(),
            'creates_clock_event' => false,
            'correction_snapshot' => [
                'source' => 'ESS Request Center',
                'requested_event_at' => $validated['requested_event_at'],
                'requested_event_type' => $validated['requested_event_type'],
                'correction_type' => $validated['correction_type'],
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('ess_attendance_correction.submitted', "ESS koreksi absensi {$correction->correction_number} diajukan oleh {$employee->employee_number}.", $correction, [], $correction->toArray(), $request);

        return redirect()->route('ess.request-center')->with('success', 'Koreksi absensi berhasil dikirim.');
    }

    private function currentEmployee(): ?EmployeeProfile
    {
        return EmployeeProfile::where('user_id', auth()->id())
            ->orWhere('work_email', auth()->user()?->email)
            ->first();
    }

    private function missingEmployeeProfileResponse(): RedirectResponse
    {
        return redirect()
            ->route('ess.request-center')
            ->withErrors('User login belum terhubung ke data karyawan. Hubungkan user ke Employee Profile sebelum mengirim request ESS.');
    }
}
