<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\IndividualDevelopmentPlan;
use App\Models\InternalMobilityApplication;
use App\Models\InternalMobilityOpportunity;
use App\Models\JobPosition;
use App\Models\SuccessionCandidate;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternalMobilityController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $opportunities = InternalMobilityOpportunity::with(['jobPosition.jobGrade', 'company', 'branch'])
            ->withCount('applications')
            ->latest('open_date')
            ->latest()
            ->limit(120)
            ->get();

        $applications = InternalMobilityApplication::with(['opportunity.jobPosition', 'employeeProfile', 'successionCandidate', 'developmentPlan'])
            ->latest('match_score')
            ->latest()
            ->limit(150)
            ->get();

        return view('performance.internal-mobility', [
            'active' => 'performance-internal-mobility',
            'positions' => JobPosition::with('jobGrade')->where('is_active', true)->orderBy('code')->get(),
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'successionCandidates' => SuccessionCandidate::with('employeeProfile')->orderByDesc('readiness_score')->get(),
            'idps' => IndividualDevelopmentPlan::with('employeeProfile')->whereIn('status', ['approved', 'in_progress'])->orderBy('plan_number')->get(),
            'opportunities' => $opportunities,
            'applications' => $applications,
            'summary' => [
                'opportunities' => $opportunities->count(),
                'published' => $opportunities->where('status', 'published')->count(),
                'applications' => $applications->count(),
                'approved' => $applications->whereIn('status', ['approved', 'offer', 'moved'])->count(),
                'avg_match' => (int) round($applications->avg('match_score') ?? 0),
            ],
        ]);
    }

    public function storeOpportunity(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'job_position_id' => ['required', 'exists:job_positions,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'opportunity_number' => ['required', 'string', 'max:100', 'unique:internal_mobility_opportunities,opportunity_number'],
            'title' => ['required', 'string', 'max:255'],
            'career_track' => ['required', 'string', 'max:120'],
            'movement_type' => ['required', Rule::in(['promotion', 'lateral_move', 'rotation', 'cross_function', 'project_assignment'])],
            'readiness_requirement' => ['required', Rule::in(['ready_now', 'ready_6_months', 'ready_12_months', 'ready_24_months', 'needs_development'])],
            'open_slots' => ['required', 'integer', 'min:1', 'max:1000'],
            'open_date' => ['nullable', 'date'],
            'close_date' => ['nullable', 'date', 'after_or_equal:open_date'],
            'eligibility_note' => ['nullable', 'string', 'max:1500'],
            'development_benefit' => ['nullable', 'string', 'max:1500'],
            'owner_name' => ['nullable', 'string', 'max:160'],
            'status' => ['required', Rule::in(['draft', 'published', 'screening', 'closed', 'cancelled'])],
        ]);

        $opportunity = InternalMobilityOpportunity::create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('internal_mobility_opportunity.created', "Opportunity {$opportunity->opportunity_number} dibuat.", $opportunity, [], $opportunity->toArray(), $request);

        return redirect()->route('performance.internal-mobility')->with('success', 'Opportunity mobilitas internal berhasil disimpan.');
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'internal_mobility_opportunity_id' => ['required', 'exists:internal_mobility_opportunities,id'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'succession_candidate_id' => ['nullable', 'exists:succession_candidates,id'],
            'individual_development_plan_id' => ['nullable', 'exists:individual_development_plans,id'],
            'application_number' => ['required', 'string', 'max:100', 'unique:internal_mobility_applications,application_number'],
            'source' => ['required', Rule::in(['employee_self_nomination', 'manager_nomination', 'hr_nomination', 'succession_pool', 'idp_recommendation'])],
            'match_score' => ['required', 'integer', 'min:0', 'max:100'],
            'readiness_level' => ['required', Rule::in(['ready_now', 'ready_6_months', 'ready_12_months', 'ready_24_months', 'needs_development'])],
            'manager_support' => ['required', Rule::in(['pending', 'supported', 'not_supported', 'conditional'])],
            'mobility_constraint' => ['nullable', 'string', 'max:255'],
            'motivation_note' => ['nullable', 'string', 'max:1500'],
            'screening_note' => ['nullable', 'string', 'max:1500'],
            'status' => ['required', Rule::in(['submitted', 'screening', 'shortlisted', 'approved', 'offer', 'moved', 'rejected', 'withdrawn'])],
            'target_movement_date' => ['nullable', 'date'],
            'owner_name' => ['nullable', 'string', 'max:160'],
        ]);

        $application = InternalMobilityApplication::create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('internal_mobility_application.created', "Application {$application->application_number} dibuat.", $application, [], $application->toArray(), $request);

        return redirect()->route('performance.internal-mobility')->with('success', 'Aplikasi mobilitas internal berhasil disimpan.');
    }
}
