<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\HolidayCalendar;
use App\Models\LeavePolicyRule;
use App\Models\LeavePolicyType;
use App\Models\LeaveRequest;
use App\Support\AuditLogger;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('view leave');

        $selectedEmployee = $request->filled('employee_profile_id')
            ? EmployeeProfile::find($request->integer('employee_profile_id'))
            : null;
        $selectedType = $request->filled('leave_policy_type_id')
            ? LeavePolicyType::find($request->integer('leave_policy_type_id'))
            : null;

        $simulation = null;
        if ($selectedEmployee && $selectedType && $request->filled(['start_date', 'end_date'])) {
            $simulation = $this->simulate($selectedEmployee, $selectedType, $request->date('start_date'), $request->date('end_date'));
        }

        $requests = LeaveRequest::with(['employeeProfile', 'policyType', 'policyRule'])
            ->latest()
            ->limit(120)
            ->get();

        return view('leave.requests', [
            'active' => 'leave-requests',
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'policyTypes' => LeavePolicyType::where('status', 'active')->orderBy('code')->get(),
            'requests' => $requests,
            'simulation' => $simulation,
            'filters' => $request->only(['employee_profile_id', 'leave_policy_type_id', 'start_date', 'end_date']),
            'summary' => [
                'total' => $requests->count(),
                'submitted' => $requests->where('approval_status', 'submitted')->count(),
                'approved' => $requests->where('approval_status', 'approved')->count(),
                'rejected' => $requests->where('approval_status', 'rejected')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('approve leave');

        $validated = $request->validate([
            'request_number' => ['required', 'string', 'max:80', 'unique:leave_requests,request_number'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'leave_policy_type_id' => ['required', 'exists:leave_policy_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'request_source' => ['required', Rule::in(['web_admin', 'ess_webview', 'mobile_webview'])],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'cancelled'])],
            'current_approver' => ['nullable', 'string', 'max:255'],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1200'],
            'decision_notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $employee = EmployeeProfile::findOrFail($validated['employee_profile_id']);
        $type = LeavePolicyType::findOrFail($validated['leave_policy_type_id']);
        $simulation = $this->simulate($employee, $type, $request->date('start_date'), $request->date('end_date'));

        $leaveRequest = LeaveRequest::create([
            ...$validated,
            'leave_policy_rule_id' => $simulation['rule_id'],
            'requested_days' => $simulation['requested_days'],
            'balance_before' => $simulation['balance_before'],
            'balance_after' => $simulation['balance_after'],
            'duration_unit' => $type->unit,
            'submitted_at' => in_array($validated['approval_status'], ['submitted', 'approved', 'rejected'], true) ? now() : null,
            'decided_at' => in_array($validated['approval_status'], ['approved', 'rejected'], true) ? now() : null,
            'decided_by' => in_array($validated['approval_status'], ['approved', 'rejected'], true) ? (auth()->user()?->name ?? 'System') : null,
            'simulation_snapshot' => $simulation,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'leave_request.created',
            "Pengajuan cuti {$leaveRequest->request_number} dibuat.",
            $leaveRequest,
            [],
            $leaveRequest->toArray(),
            $request,
        );

        return redirect()->route('leave.requests')->with('success', 'Pengajuan cuti berhasil dibuat.');
    }

    private function simulate(EmployeeProfile $employee, LeavePolicyType $type, mixed $startDate, mixed $endDate): array
    {
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->startOfDay();
        $rule = LeavePolicyRule::where('leave_policy_type_id', $type->id)
            ->where('status', 'active')
            ->where('effective_start', '<=', $start)
            ->where(fn ($query) => $query->whereNull('effective_end')->orWhere('effective_end', '>=', $end))
            ->where(fn ($query) => $query->whereNull('company_id')->orWhere('company_id', $employee->company_id))
            ->where(fn ($query) => $query->whereNull('branch_id')->orWhere('branch_id', $employee->branch_id))
            ->where(fn ($query) => $query->whereNull('employment_status')->orWhere('employment_status', $employee->employment_status))
            ->latest('effective_start')
            ->first();

        $includeWeekends = $rule?->include_weekends ?? false;
        $includeHolidays = $rule?->include_holidays ?? false;
        $holidayDates = $includeHolidays ? [] : HolidayCalendar::whereBetween('holiday_date', [$start, $end])
            ->where('is_active', true)
            ->pluck('holiday_date')
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->toDateString())
            ->all();

        $days = 0.0;
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (! $includeWeekends && $date->isWeekend()) {
                continue;
            }
            if (! $includeHolidays && in_array($date->toDateString(), $holidayDates, true)) {
                continue;
            }
            $days += 1;
        }

        $usedDays = LeaveRequest::where('employee_profile_id', $employee->id)
            ->where('leave_policy_type_id', $type->id)
            ->whereIn('approval_status', ['submitted', 'approved'])
            ->sum('requested_days');
        $quota = (float) ($rule?->accrual_days ?: $type->annual_quota_days);
        $balanceBefore = max(0, $quota - (float) $usedDays);
        $balanceAfter = $type->requires_balance ? $balanceBefore - $days : $balanceBefore;

        return [
            'rule_id' => $rule?->id,
            'policy_code' => $type->code,
            'quota_days' => $quota,
            'used_days' => (float) $usedDays,
            'requested_days' => $days,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'include_weekends' => $includeWeekends,
            'include_holidays' => $includeHolidays,
            'needs_attachment' => $type->requires_attachment,
            'needs_approval' => $type->approval_required,
            'is_over_balance' => $type->requires_balance && $balanceAfter < 0,
        ];
    }
}
