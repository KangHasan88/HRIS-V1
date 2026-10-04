<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\OvertimeRequest;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OvertimeRequestController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view attendance');

        $requests = OvertimeRequest::with('employeeProfile')->latest()->limit(120)->get();

        return view('attendance.overtime-requests', [
            'active' => 'overtime-requests',
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'requests' => $requests,
            'summary' => [
                'total' => $requests->count(),
                'submitted' => $requests->where('approval_status', 'submitted')->count(),
                'approved' => $requests->where('approval_status', 'approved')->count(),
                'payroll' => $requests->where('handoff_to_payroll', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('approve attendance');

        $validated = $request->validate([
            'request_number' => ['required', 'string', 'max:80', 'unique:overtime_requests,request_number'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'overtime_date' => ['required', 'date'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'overtime_type' => ['required', Rule::in(['regular_day', 'holiday', 'rest_day', 'night_shift'])],
            'request_source' => ['required', Rule::in(['web_admin', 'ess_webview', 'mobile_webview'])],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'cancelled'])],
            'current_approver' => ['nullable', 'string', 'max:255'],
            'hourly_rate' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'payroll_component_code' => ['nullable', 'string', 'max:80'],
            'handoff_to_payroll' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:1200'],
            'decision_notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $calculation = $this->calculate($validated);
        $isDecided = in_array($validated['approval_status'], ['approved', 'rejected'], true);
        $handoff = $request->boolean('handoff_to_payroll') && $validated['approval_status'] === 'approved';

        $overtime = OvertimeRequest::create([
            ...$validated,
            'requested_hours' => $calculation['requested_hours'],
            'approved_hours' => $validated['approval_status'] === 'approved' ? $calculation['requested_hours'] : 0,
            'payroll_multiplier' => $calculation['multiplier'],
            'estimated_amount' => $validated['approval_status'] === 'approved' ? $calculation['estimated_amount'] : 0,
            'handoff_to_payroll' => $handoff,
            'payroll_handoff_at' => $handoff ? now() : null,
            'submitted_at' => in_array($validated['approval_status'], ['submitted', 'approved', 'rejected'], true) ? now() : null,
            'decided_at' => $isDecided ? now() : null,
            'decided_by' => $isDecided ? (auth()->user()?->name ?? 'System') : null,
            'calculation_snapshot' => $calculation,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'overtime_request.created',
            "Pengajuan lembur {$overtime->request_number} dibuat.",
            $overtime,
            [],
            $overtime->toArray(),
            $request,
        );

        return redirect()->route('attendance.overtime-requests')->with('success', 'Pengajuan lembur berhasil dibuat.');
    }

    private function calculate(array $data): array
    {
        $start = \Carbon\Carbon::parse($data['start_at']);
        $end = \Carbon\Carbon::parse($data['end_at']);
        $hours = round($start->diffInMinutes($end) / 60, 2);
        $multiplier = match ($data['overtime_type']) {
            'holiday' => 2.0,
            'rest_day' => 1.75,
            'night_shift' => 1.5,
            default => 1.5,
        };
        $estimated = round($hours * (float) $data['hourly_rate'] * $multiplier, 2);

        return [
            'requested_hours' => $hours,
            'multiplier' => $multiplier,
            'hourly_rate' => (float) $data['hourly_rate'],
            'estimated_amount' => $estimated,
            'formula' => 'approved_hours x hourly_rate x multiplier',
            'overtime_type' => $data['overtime_type'],
        ];
    }
}
