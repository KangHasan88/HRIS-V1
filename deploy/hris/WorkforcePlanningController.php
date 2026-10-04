<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\RecruitmentManpowerRequest;
use App\Models\WorkforcePlanningSimulation;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkforcePlanningController extends Controller
{
    public function index(): View
    {
        $this->authorizeAnalytics();

        $simulations = WorkforcePlanningSimulation::with(['company', 'branch'])->latest()->limit(120)->get();
        $openManpower = RecruitmentManpowerRequest::whereIn('status', ['draft', 'submitted', 'approved'])->sum('headcount');

        return view('analytics.workforce-planning', [
            'active' => 'workforce-planning',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'simulations' => $simulations,
            'summary' => [
                'active_employee' => EmployeeProfile::where('is_active', true)->count(),
                'open_manpower' => $openManpower,
                'simulations' => $simulations->count(),
                'hybrid' => $simulations->where('recommended_scenario', 'hybrid')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAnalytics();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'simulation_number' => ['required', 'string', 'max:80', 'unique:workforce_planning_simulations,simulation_number'],
            'scenario_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:120'],
            'current_headcount' => ['required', 'integer', 'min:0', 'max:100000'],
            'required_headcount' => ['required', 'integer', 'min:0', 'max:100000'],
            'hire_headcount' => ['required', 'integer', 'min:0', 'max:100000'],
            'monthly_salary_budget' => ['required', 'numeric', 'min:0'],
            'overtime_hourly_rate' => ['required', 'numeric', 'min:0'],
            'overtime_hours_per_week' => ['required', 'numeric', 'min:0', 'max:168'],
            'planning_months' => ['required', 'integer', 'min:1', 'max:60'],
            'hiring_cost' => ['nullable', 'numeric', 'min:0'],
            'training_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $gap = max(0, (int) $validated['required_headcount'] - (int) $validated['current_headcount']);
        $hire = min((int) $validated['hire_headcount'], $gap);
        $remainingGap = max(0, $gap - $hire);
        $months = (int) $validated['planning_months'];
        $salary = (float) $validated['monthly_salary_budget'];
        $hiringCost = (float) ($validated['hiring_cost'] ?? 0);
        $trainingCost = (float) ($validated['training_cost'] ?? 0);
        $otRate = (float) $validated['overtime_hourly_rate'];
        $otHours = (float) $validated['overtime_hours_per_week'];

        $hireScenario = ($gap * $salary * $months) + ($gap * ($hiringCost + $trainingCost));
        $overtimeScenario = $gap * $otRate * $otHours * 4.33 * $months;
        $hybridScenario = ($hire * $salary * $months) + ($hire * ($hiringCost + $trainingCost)) + ($remainingGap * $otRate * $otHours * 4.33 * $months);

        $costs = [
            'hire' => $hireScenario,
            'overtime' => $overtimeScenario,
            'hybrid' => $hybridScenario,
        ];
        asort($costs);
        $recommended = array_key_first($costs);

        $simulation = WorkforcePlanningSimulation::create([
            ...$validated,
            'hire_scenario_cost' => round($hireScenario, 2),
            'overtime_scenario_cost' => round($overtimeScenario, 2),
            'hybrid_scenario_cost' => round($hybridScenario, 2),
            'recommended_scenario' => $recommended,
            'assumption_snapshot' => [
                'gap_headcount' => $gap,
                'hybrid_hire_headcount' => $hire,
                'hybrid_overtime_gap' => $remainingGap,
                'weeks_per_month' => 4.33,
                'formula' => [
                    'hire' => 'gap x monthly_salary x months + gap x hiring/training cost',
                    'overtime' => 'gap x overtime_rate x overtime_hours_per_week x 4.33 x months',
                    'hybrid' => 'hire partial + overtime untuk remaining gap',
                ],
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('workforce_planning_simulation.created', "Simulasi workforce {$simulation->simulation_number} dibuat.", $simulation, [], $simulation->toArray(), $request);

        return redirect()->route('analytics.workforce-planning')->with('success', 'Simulasi workforce berhasil dibuat.');
    }

    private function authorizeAnalytics(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance') || $user?->hasPermission('manage modules'), 403);
    }
}
