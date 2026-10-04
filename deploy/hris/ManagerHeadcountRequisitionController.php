<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\ManagerHeadcountPlan;
use App\Models\ManagerReplacementRequisition;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagerHeadcountRequisitionController extends Controller
{
    public function index(): View
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $team = $this->teamFor($manager);
        $teamIds = $team->pluck('id');

        $plans = ManagerHeadcountPlan::with(['company', 'branch'])
            ->where('manager_user_id', $manager->id)
            ->latest('planning_period')
            ->limit(80)
            ->get();

        $requisitions = ManagerReplacementRequisition::with(['employeeProfile', 'company', 'branch'])
            ->where('manager_user_id', $manager->id)
            ->where(fn ($query) => $query->whereNull('employee_profile_id')->orWhereIn('employee_profile_id', $teamIds))
            ->latest('target_join_date')
            ->limit(80)
            ->get();

        return view('manager.headcount-requisitions', [
            'active' => 'manager-headcount-requisitions',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'team' => $team,
            'plans' => $plans,
            'requisitions' => $requisitions,
            'summary' => [
                'team_total' => $team->count(),
                'open_plans' => $plans->whereIn('approval_status', ['draft', 'submitted', 'review'])->count(),
                'open_requisitions' => $requisitions->whereIn('approval_status', ['draft', 'submitted', 'review'])->count(),
                'requested_headcount' => $plans->whereIn('approval_status', ['draft', 'submitted', 'review', 'approved'])->sum('requested_headcount'),
            ],
        ]);
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $validated = $request->validate([
            'plan_number' => ['required', 'string', 'max:80', 'unique:manager_headcount_plans,plan_number'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department' => ['nullable', 'string', 'max:160'],
            'position_title' => ['required', 'string', 'max:160'],
            'request_type' => ['required', Rule::in(['new_headcount', 'expansion', 'seasonal', 'replacement_pool'])],
            'current_headcount' => ['required', 'integer', 'min:0', 'max:10000'],
            'requested_headcount' => ['required', 'integer', 'min:1', 'max:10000'],
            'planning_period' => ['required', 'date'],
            'target_join_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'budget_estimate' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'budget_status' => ['required', Rule::in(['not_checked', 'within_budget', 'over_budget', 'needs_finance_review'])],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'review', 'approved', 'rejected', 'cancelled'])],
            'current_approver' => ['nullable', 'string', 'max:160'],
            'business_reason' => ['required', 'string', 'max:2400'],
            'risk_if_not_filled' => ['nullable', 'string', 'max:2400'],
        ]);

        $plan = ManagerHeadcountPlan::create([
            ...$validated,
            'manager_user_id' => $manager->id,
            'budget_estimate' => $validated['budget_estimate'] ?? 0,
            'created_by' => $manager->id,
        ]);

        AuditLogger::record(
            'manager_headcount_plan.created',
            "Headcount plan {$plan->plan_number} dibuat.",
            $plan,
            [],
            $plan->toArray(),
            $request,
        );

        return redirect()->route('manager.headcount-requisitions')->with('success', 'Rencana headcount berhasil disimpan.');
    }

    public function storeReplacement(Request $request): RedirectResponse
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $team = $this->teamFor($manager);
        $teamIds = $team->pluck('id');
        $validated = $request->validate([
            'requisition_number' => ['required', 'string', 'max:80', 'unique:manager_replacement_requisitions,requisition_number'],
            'employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'position_title' => ['required', 'string', 'max:160'],
            'replacement_type' => ['required', Rule::in(['resignation', 'transfer', 'termination', 'promotion', 'temporary_backfill'])],
            'vacancy_date' => ['nullable', 'date'],
            'target_join_date' => ['nullable', 'date'],
            'employment_type' => ['required', Rule::in(['permanent', 'contract', 'daily_worker', 'intern', 'outsourcing'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'review', 'approved', 'rejected', 'cancelled'])],
            'current_approver' => ['nullable', 'string', 'max:160'],
            'skill_requirements' => ['nullable', 'string', 'max:1200'],
            'replacement_reason' => ['required', 'string', 'max:2400'],
            'handover_notes' => ['nullable', 'string', 'max:2400'],
        ]);

        if (! empty($validated['employee_profile_id'])) {
            abort_unless($teamIds->contains((int) $validated['employee_profile_id']), 403);
            $employee = $team->firstWhere('id', (int) $validated['employee_profile_id']);
        } else {
            $employee = null;
        }

        $requisition = ManagerReplacementRequisition::create([
            ...collect($validated)->except('skill_requirements')->all(),
            'manager_user_id' => $manager->id,
            'company_id' => $employee?->company_id,
            'branch_id' => $employee?->branch_id,
            'skill_requirements' => $this->splitList($validated['skill_requirements'] ?? null),
            'created_by' => $manager->id,
        ]);

        AuditLogger::record(
            'manager_replacement_requisition.created',
            "Replacement requisition {$requisition->requisition_number} dibuat.",
            $requisition,
            [],
            $requisition->toArray(),
            $request,
        );

        return redirect()->route('manager.headcount-requisitions')->with('success', 'Requisition pengganti berhasil disimpan.');
    }

    private function teamFor($manager): Collection
    {
        $managerEmployee = EmployeeProfile::where('user_id', $manager?->id)
            ->orWhere('work_email', $manager?->email)
            ->first();

        $query = EmployeeProfile::with(['branch', 'company'])
            ->where('is_active', true)
            ->orderBy('full_name');

        if ($managerEmployee?->branch_id && ! $manager?->hasRole('Super Admin')) {
            $query->where('branch_id', $managerEmployee->branch_id);
        } elseif ($manager?->branch_id && ! $manager?->hasRole('Super Admin')) {
            $query->where('branch_id', $manager->branch_id);
        }

        return $query->limit(150)->get();
    }

    private function splitList(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
