<?php

namespace App\Http\Controllers;

use App\Models\CompetencyRoleRequirement;
use App\Models\EmployeeProfile;
use App\Models\IndividualDevelopmentPlan;
use App\Models\TrainingCourse;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IndividualDevelopmentPlanController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $plans = IndividualDevelopmentPlan::with([
            'employeeProfile.branch',
            'competencyRequirement.competency',
            'trainingCourse',
        ])
            ->latest('target_date')
            ->latest()
            ->limit(150)
            ->get();

        $employees = EmployeeProfile::where('is_active', true)
            ->orderBy('employee_number')
            ->get();

        return view('performance.individual-development-plans', [
            'active' => 'performance-idp',
            'employees' => $employees,
            'requirements' => CompetencyRoleRequirement::with(['competency', 'jobPosition', 'jobGrade', 'trainingCourse'])
                ->where('status', 'active')
                ->orderBy('requirement_number')
                ->limit(200)
                ->get(),
            'courses' => TrainingCourse::whereIn('status', ['published', 'draft'])
                ->orderBy('course_code')
                ->get(),
            'plans' => $plans,
            'summary' => [
                'plans' => $plans->count(),
                'active' => $plans->whereIn('status', ['approved', 'in_progress'])->count(),
                'completed' => $plans->where('status', 'completed')->count(),
                'high_priority' => $plans->whereIn('priority', ['high', 'critical'])->count(),
                'avg_progress' => (int) round($plans->avg('progress_percent') ?? 0),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'competency_role_requirement_id' => ['nullable', 'exists:competency_role_requirements,id'],
            'training_course_id' => ['nullable', 'exists:training_courses,id'],
            'plan_number' => ['required', 'string', 'max:100', 'unique:individual_development_plans,plan_number'],
            'development_area' => ['required', 'string', 'max:255'],
            'career_goal' => ['nullable', 'string', 'max:255'],
            'current_level' => ['required', 'integer', 'min:1', 'max:5'],
            'target_level' => ['required', 'integer', 'min:1', 'max:5'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'learning_recommendation' => ['nullable', 'string', 'max:255'],
            'action_plan' => ['nullable', 'string', 'max:1500'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'mentor_name' => ['nullable', 'string', 'max:160'],
            'reviewer_name' => ['nullable', 'string', 'max:160'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['draft', 'approved', 'in_progress', 'completed', 'paused', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $plan = IndividualDevelopmentPlan::create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'individual_development_plan.created',
            "IDP {$plan->plan_number} dibuat.",
            $plan,
            [],
            $plan->toArray(),
            $request,
        );

        return redirect()->route('performance.idp')->with('success', 'Individual Development Plan berhasil disimpan.');
    }
}
