<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\IndividualDevelopmentPlan;
use App\Models\JobPosition;
use App\Models\SuccessionCandidate;
use App\Models\SuccessionPlan;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuccessionPlanningController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $plans = SuccessionPlan::with(['jobPosition.jobGrade', 'incumbent', 'candidates.employeeProfile'])
            ->latest('target_ready_by')
            ->latest()
            ->limit(120)
            ->get();

        $candidates = SuccessionCandidate::with(['successionPlan.jobPosition', 'employeeProfile', 'developmentPlan'])
            ->latest('readiness_score')
            ->latest()
            ->limit(160)
            ->get();

        return view('performance.succession-planning', [
            'active' => 'performance-succession',
            'positions' => JobPosition::with('jobGrade')->where('is_active', true)->orderBy('code')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'idps' => IndividualDevelopmentPlan::with('employeeProfile')->whereIn('status', ['approved', 'in_progress'])->orderBy('plan_number')->get(),
            'plans' => $plans,
            'candidates' => $candidates,
            'summary' => [
                'plans' => $plans->count(),
                'critical' => $plans->whereIn('criticality', ['high', 'critical'])->count(),
                'ready_now' => $candidates->where('readiness_level', 'ready_now')->count(),
                'ready_12' => $candidates->where('readiness_level', 'ready_12_months')->count(),
                'avg_score' => (int) round($candidates->avg('readiness_score') ?? 0),
            ],
        ]);
    }

    public function storePlan(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'job_position_id' => ['required', 'exists:job_positions,id'],
            'incumbent_employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'plan_number' => ['required', 'string', 'max:100', 'unique:succession_plans,plan_number'],
            'criticality' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'vacancy_risk' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'target_ready_by' => ['nullable', 'date'],
            'business_impact' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'active', 'monitoring', 'closed', 'paused'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $plan = SuccessionPlan::create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'succession_plan.created',
            "Succession plan {$plan->plan_number} dibuat.",
            $plan,
            [],
            $plan->toArray(),
            $request,
        );

        return redirect()->route('performance.succession')->with('success', 'Succession plan berhasil disimpan.');
    }

    public function storeCandidate(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'succession_plan_id' => ['required', 'exists:succession_plans,id'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'individual_development_plan_id' => ['nullable', 'exists:individual_development_plans,id'],
            'candidate_number' => ['required', 'string', 'max:100', 'unique:succession_candidates,candidate_number'],
            'readiness_level' => ['required', Rule::in(['ready_now', 'ready_6_months', 'ready_12_months', 'ready_24_months', 'needs_development'])],
            'readiness_score' => ['required', 'integer', 'min:0', 'max:100'],
            'performance_rating' => ['required', Rule::in(['below', 'meets', 'exceeds', 'outstanding'])],
            'potential_rating' => ['required', Rule::in(['low', 'medium', 'high'])],
            'mobility_preference' => ['required', Rule::in(['same_site', 'cross_site', 'cross_company', 'remote', 'not_mobile'])],
            'retention_risk' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'development_gap' => ['nullable', 'string', 'max:1500'],
            'next_action' => ['nullable', 'string', 'max:1500'],
            'owner_name' => ['nullable', 'string', 'max:160'],
            'review_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['nominated', 'validated', 'in_development', 'ready', 'rejected', 'moved'])],
        ]);

        $candidate = SuccessionCandidate::create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'succession_candidate.created',
            "Succession candidate {$candidate->candidate_number} dibuat.",
            $candidate,
            [],
            $candidate->toArray(),
            $request,
        );

        return redirect()->route('performance.succession')->with('success', 'Kandidat succession berhasil disimpan.');
    }
}
