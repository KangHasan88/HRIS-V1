<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\PerformanceGoal;
use App\Models\PerformanceGoalCheckIn;
use App\Models\PerformanceGoalCycle;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerformanceGoalOkrController extends Controller
{
    public function index(): View
    {
        $this->authorizePerformance();

        $cycles = PerformanceGoalCycle::with(['company', 'branch'])->withCount('goals')->latest('period_start')->limit(80)->get();
        $goals = PerformanceGoal::with(['cycle', 'employeeProfile', 'manager', 'parentGoal'])
            ->withCount(['childGoals', 'checkIns'])
            ->latest()
            ->limit(120)
            ->get();
        $checkIns = PerformanceGoalCheckIn::with(['goal.employeeProfile', 'employeeProfile', 'reviewer'])->latest('checkin_date')->limit(120)->get();

        return view('performance.goal-okr', [
            'active' => 'performance-goal-okr',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'users' => User::orderBy('name')->get(),
            'cycles' => $cycles,
            'goals' => $goals,
            'checkIns' => $checkIns,
            'summary' => [
                'cycles' => $cycles->count(),
                'active_cycles' => $cycles->where('status', 'active')->count(),
                'goals' => $goals->count(),
                'on_track' => $goals->where('status', 'on_track')->count(),
                'at_risk' => $goals->whereIn('status', ['at_risk', 'blocked'])->count(),
                'avg_progress' => round((float) $goals->avg('progress_percent'), 1),
                'avg_score' => round((float) $goals->avg('score'), 1),
                'checkins' => $checkIns->count(),
            ],
        ]);
    }

    public function storeCycle(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'cycle_code' => ['required', 'string', 'max:80', 'unique:performance_goal_cycles,cycle_code'],
            'cycle_name' => ['required', 'string', 'max:180'],
            'period_type' => ['required', Rule::in(['month', 'quarter', 'semester', 'year', 'project'])],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'checkin_deadline' => ['nullable', 'date'],
            'scoring_method' => ['required', Rule::in(['weighted_progress', 'manual_score', 'hybrid'])],
            'status' => ['required', Rule::in(['draft', 'active', 'closed', 'cancelled'])],
            'calibration_rules' => ['nullable', 'string', 'max:1200'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $cycle = PerformanceGoalCycle::create([
            ...collect($validated)->except('calibration_rules')->all(),
            'calibration_rules' => $this->splitList($validated['calibration_rules'] ?? null),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('performance_goal_cycle.created', "Siklus OKR {$cycle->cycle_code} dibuat.", $cycle, [], $cycle->toArray(), $request);

        return redirect()->route('performance.goal-okr')->with('success', 'Siklus OKR berhasil dibuat.');
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'performance_goal_cycle_id' => ['required', 'exists:performance_goal_cycles,id'],
            'employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'manager_user_id' => ['nullable', 'exists:users,id'],
            'parent_goal_id' => ['nullable', 'exists:performance_goals,id'],
            'goal_number' => ['required', 'string', 'max:80', 'unique:performance_goals,goal_number'],
            'goal_type' => ['required', Rule::in(['company', 'department', 'team', 'individual', 'project'])],
            'objective_title' => ['required', 'string', 'max:220'],
            'objective_description' => ['nullable', 'string', 'max:2400'],
            'key_result_metric' => ['required', 'string', 'max:220'],
            'baseline_value' => ['nullable', 'numeric'],
            'target_value' => ['required', 'numeric', 'gt:baseline_value'],
            'current_value' => ['nullable', 'numeric'],
            'weight' => ['required', 'numeric', 'min:1', 'max:100'],
            'confidence_level' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['required', Rule::in(['draft', 'active', 'on_track', 'at_risk', 'blocked', 'completed', 'cancelled'])],
            'cascading_snapshot' => ['nullable', 'string', 'max:1200'],
            'notes' => ['nullable', 'string', 'max:2400'],
        ]);

        $baseline = (float) ($validated['baseline_value'] ?? 0);
        $target = (float) $validated['target_value'];
        $current = (float) ($validated['current_value'] ?? $baseline);
        $progress = $this->calculateProgress($baseline, $target, $current);

        $goal = PerformanceGoal::create([
            ...collect($validated)->except(['cascading_snapshot', 'baseline_value', 'current_value'])->all(),
            'baseline_value' => $baseline,
            'current_value' => $current,
            'progress_percent' => $progress,
            'score' => round($progress * ((float) $validated['weight'] / 100), 2),
            'cascading_snapshot' => $this->splitList($validated['cascading_snapshot'] ?? null),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('performance_goal.created', "Goal {$goal->goal_number} dibuat.", $goal, [], $goal->toArray(), $request);

        return redirect()->route('performance.goal-okr')->with('success', 'Goal/OKR berhasil dibuat.');
    }

    public function storeCheckIn(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'performance_goal_id' => ['required', 'exists:performance_goals,id'],
            'employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'reviewer_user_id' => ['nullable', 'exists:users,id'],
            'checkin_number' => ['required', 'string', 'max:80', 'unique:performance_goal_check_ins,checkin_number'],
            'checkin_date' => ['required', 'date'],
            'new_value' => ['required', 'numeric'],
            'confidence_level' => ['required', Rule::in(['low', 'medium', 'high'])],
            'risk_status' => ['required', Rule::in(['on_track', 'at_risk', 'blocked'])],
            'achievement_notes' => ['nullable', 'string', 'max:2400'],
            'blocker_notes' => ['nullable', 'string', 'max:2400'],
            'next_action' => ['nullable', 'string', 'max:2400'],
            'status' => ['required', Rule::in(['draft', 'submitted', 'reviewed', 'returned'])],
        ]);

        $goal = PerformanceGoal::findOrFail($validated['performance_goal_id']);
        $progress = $this->calculateProgress((float) $goal->baseline_value, (float) $goal->target_value, (float) $validated['new_value']);

        $checkIn = PerformanceGoalCheckIn::create([
            ...$validated,
            'employee_profile_id' => $validated['employee_profile_id'] ?? $goal->employee_profile_id,
            'previous_value' => $goal->current_value,
            'progress_percent' => $progress,
            'reviewed_at' => $validated['status'] === 'reviewed' ? now() : null,
            'created_by' => auth()->id(),
        ]);

        $goal->forceFill([
            'current_value' => $validated['new_value'],
            'progress_percent' => $progress,
            'score' => round($progress * ((float) $goal->weight / 100), 2),
            'confidence_level' => $validated['confidence_level'],
            'status' => $validated['risk_status'] === 'blocked' ? 'blocked' : ($validated['risk_status'] === 'at_risk' ? 'at_risk' : 'on_track'),
        ])->save();

        AuditLogger::record('performance_goal_checkin.created', "Check-in {$checkIn->checkin_number} dibuat.", $checkIn, [], $checkIn->toArray(), $request);

        return redirect()->route('performance.goal-okr')->with('success', 'Check-in goal berhasil disimpan.');
    }

    private function authorizePerformance(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage recruitment') || $user?->hasPermission('manage modules') || $user?->hasPermission('view attendance'), 403);
    }

    private function splitList(?string $value): array
    {
        return $value ? collect(explode(',', $value))->map(fn ($item) => trim($item))->filter()->values()->all() : [];
    }

    private function calculateProgress(float $baseline, float $target, float $current): float
    {
        if ($target <= $baseline) {
            return 0;
        }

        return round(max(0, min(100, (($current - $baseline) / ($target - $baseline)) * 100)), 2);
    }
}
