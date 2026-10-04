<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\ManagerBudgetAllocation;
use App\Models\ManagerClaimRequest;
use App\Models\OvertimeRequest;
use App\Models\TrainingEnrollment;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagerBudgetController extends Controller
{
    public function index(Request $request): View
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $start = $request->filled('start_date') ? Carbon::parse($request->date('start_date'))->startOfDay() : now()->startOfMonth();
        $end = $request->filled('end_date') ? Carbon::parse($request->date('end_date'))->startOfDay() : now()->endOfMonth();
        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $team = $this->teamFor($manager);
        $teamIds = $team->pluck('id');

        $allocations = ManagerBudgetAllocation::with(['company', 'branch', 'manager'])
            ->whereDate('period_start', '<=', $end)
            ->whereDate('period_end', '>=', $start)
            ->where(fn ($query) => $query->whereNull('manager_user_id')->orWhere('manager_user_id', $manager->id))
            ->latest('period_start')
            ->get();

        $claims = ManagerClaimRequest::with('employeeProfile')
            ->whereIn('employee_profile_id', $teamIds)
            ->whereBetween('claim_date', [$start, $end])
            ->latest('claim_date')
            ->get();

        $overtimes = OvertimeRequest::with('employeeProfile')
            ->whereIn('employee_profile_id', $teamIds)
            ->whereBetween('overtime_date', [$start, $end])
            ->latest('overtime_date')
            ->get();

        $trainings = TrainingEnrollment::with(['employeeProfile', 'course'])
            ->whereIn('employee_profile_id', $teamIds)
            ->whereHas('course', fn ($query) => $query->whereDate('start_date', '<=', $end)->whereDate('end_date', '>=', $start))
            ->latest()
            ->get();

        $budgetRows = collect(['overtime', 'claim', 'training'])->map(function (string $category) use ($allocations, $claims, $overtimes, $trainings) {
            $allocated = (float) $allocations->where('category', $category)->sum('allocated_amount');
            $actual = match ($category) {
                'overtime' => (float) $overtimes->whereIn('approval_status', ['submitted', 'approved'])->sum('estimated_amount'),
                'claim' => (float) $claims->whereIn('approval_status', ['submitted', 'approved'])->reject(fn ($claim) => $claim->claim_type === 'training')->sum('amount'),
                'training' => (float) $claims->where('claim_type', 'training')->whereIn('approval_status', ['submitted', 'approved'])->sum('amount'),
            };

            $threshold = (int) ($allocations->where('category', $category)->max('alert_threshold_percent') ?: 80);
            $usage = $allocated > 0 ? round(($actual / $allocated) * 100, 1) : 0;

            return [
                'category' => $category,
                'label' => match ($category) {
                    'overtime' => 'Lembur',
                    'claim' => 'Klaim',
                    default => 'Training',
                },
                'allocated' => $allocated,
                'actual' => $actual,
                'remaining' => $allocated - $actual,
                'usage' => $usage,
                'threshold' => $threshold,
                'status' => $allocated > 0 && $usage >= $threshold ? 'warning' : 'ok',
            ];
        });

        return view('manager.budget-visibility', [
            'active' => 'manager-budget',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'team' => $team,
            'allocations' => $allocations,
            'claims' => $claims,
            'overtimes' => $overtimes,
            'trainings' => $trainings,
            'budgetRows' => $budgetRows,
            'filters' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            'summary' => [
                'allocated' => $budgetRows->sum('allocated'),
                'actual' => $budgetRows->sum('actual'),
                'remaining' => $budgetRows->sum('remaining'),
                'warning' => $budgetRows->where('status', 'warning')->count(),
            ],
        ]);
    }

    public function storeAllocation(Request $request): RedirectResponse
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $validated = $request->validate([
            'allocation_number' => ['required', 'string', 'max:80', 'unique:manager_budget_allocations,allocation_number'],
            'category' => ['required', Rule::in(['overtime', 'claim', 'training'])],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'allocated_amount' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'alert_threshold_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $allocation = ManagerBudgetAllocation::create([
            ...$validated,
            'manager_user_id' => $manager->id,
            'created_by' => $manager->id,
        ]);

        AuditLogger::record('manager_budget_allocation.created', "Budget manager {$allocation->allocation_number} dibuat.", $allocation, [], $allocation->toArray(), $request);

        return redirect()->route('manager.budget')->with('success', 'Budget manager berhasil disimpan.');
    }

    public function storeClaim(Request $request): RedirectResponse
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $validated = $request->validate([
            'claim_number' => ['required', 'string', 'max:80', 'unique:manager_claim_requests,claim_number'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'claim_date' => ['required', 'date'],
            'claim_type' => ['required', Rule::in(['transport', 'meal', 'medical', 'training', 'other'])],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'cancelled'])],
            'current_approver' => ['nullable', 'string', 'max:255'],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1200'],
        ]);

        $employee = EmployeeProfile::findOrFail($validated['employee_profile_id']);
        $claim = ManagerClaimRequest::create([
            ...$validated,
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'submitted_at' => in_array($validated['approval_status'], ['submitted', 'approved', 'rejected'], true) ? now() : null,
            'decided_at' => in_array($validated['approval_status'], ['approved', 'rejected'], true) ? now() : null,
            'decided_by' => in_array($validated['approval_status'], ['approved', 'rejected'], true) ? ($manager->name ?? 'System') : null,
            'created_by' => $manager->id,
        ]);

        AuditLogger::record('manager_claim_request.created', "Klaim {$claim->claim_number} dibuat.", $claim, [], $claim->toArray(), $request);

        return redirect()->route('manager.budget')->with('success', 'Klaim budget berhasil disimpan.');
    }

    private function teamFor($manager)
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
}
