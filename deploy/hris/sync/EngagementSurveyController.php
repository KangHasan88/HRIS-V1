<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\EngagementActionPlan;
use App\Models\EngagementSurvey;
use App\Models\EngagementSurveyResponse;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EngagementSurveyController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $surveys = EngagementSurvey::with(['company', 'branch'])->withCount(['responses', 'actionPlans'])->latest('start_date')->latest()->limit(120)->get();
        $responses = EngagementSurveyResponse::with(['survey', 'employeeProfile'])->latest('submitted_at')->latest()->limit(150)->get();
        $actions = EngagementActionPlan::with('survey')->latest('target_date')->latest()->limit(150)->get();

        return view('performance.engagement-surveys', [
            'active' => 'performance-engagement',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'surveys' => $surveys,
            'responses' => $responses,
            'actions' => $actions,
            'summary' => [
                'surveys' => $surveys->count(),
                'responses' => $responses->count(),
                'avg_engagement' => (int) round($responses->avg('engagement_score') ?? 0),
                'avg_enps' => (int) round($responses->avg('enps_score') ?? 0),
                'open_actions' => $actions->whereIn('status', ['open', 'in_progress'])->count(),
            ],
        ]);
    }

    public function storeSurvey(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'survey_number' => ['required', 'string', 'max:100', 'unique:engagement_surveys,survey_number'],
            'title' => ['required', 'string', 'max:255'],
            'survey_type' => ['required', Rule::in(['pulse', 'engagement', 'enps', 'culture', 'exit_signal'])],
            'audience_scope' => ['required', Rule::in(['all_employee', 'company', 'branch', 'department', 'manager_team'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'question_bank' => ['nullable', 'string', 'max:1500'],
            'is_anonymous' => ['nullable', 'boolean'],
            'owner_name' => ['nullable', 'string', 'max:160'],
            'status' => ['required', Rule::in(['draft', 'published', 'running', 'closed', 'archived'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $survey = EngagementSurvey::create([
            ...collect($data)->except(['question_bank', 'is_anonymous'])->all(),
            'question_bank' => collect(explode(',', (string) ($data['question_bank'] ?? '')))->map(fn ($item) => trim($item))->filter()->values()->all(),
            'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('engagement_survey.created', "Survey {$survey->survey_number} dibuat.", $survey, [], $survey->toArray(), $request);

        return redirect()->route('performance.engagement')->with('success', 'Survey engagement berhasil disimpan.');
    }

    public function storeResponse(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'engagement_survey_id' => ['required', 'exists:engagement_surveys,id'],
            'employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'response_number' => ['required', 'string', 'max:100', 'unique:engagement_survey_responses,response_number'],
            'engagement_score' => ['required', 'integer', 'min:0', 'max:100'],
            'enps_score' => ['required', 'integer', 'min:0', 'max:10'],
            'sentiment' => ['required', Rule::in(['positive', 'neutral', 'negative', 'mixed'])],
            'dimension_scores' => ['nullable', 'string', 'max:500'],
            'comment' => ['nullable', 'string', 'max:1500'],
            'status' => ['required', Rule::in(['submitted', 'reviewed', 'excluded'])],
        ]);

        $response = EngagementSurveyResponse::create([
            ...collect($data)->except(['dimension_scores'])->all(),
            'dimension_scores' => collect(explode(',', (string) ($data['dimension_scores'] ?? '')))->map(fn ($item) => trim($item))->filter()->values()->all(),
            'submitted_at' => now(),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('engagement_survey_response.created', "Response {$response->response_number} dibuat.", $response, [], $response->toArray(), $request);

        return redirect()->route('performance.engagement')->with('success', 'Response survey berhasil disimpan.');
    }

    public function storeAction(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'engagement_survey_id' => ['required', 'exists:engagement_surveys,id'],
            'action_number' => ['required', 'string', 'max:100', 'unique:engagement_action_plans,action_number'],
            'focus_area' => ['required', 'string', 'max:255'],
            'root_cause' => ['nullable', 'string', 'max:255'],
            'action_plan' => ['nullable', 'string', 'max:1500'],
            'owner_name' => ['nullable', 'string', 'max:160'],
            'target_date' => ['nullable', 'date'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'impact_metric' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'completed', 'paused', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $action = EngagementActionPlan::create([...$data, 'created_by' => auth()->id()]);

        AuditLogger::record('engagement_action_plan.created', "Action plan {$action->action_number} dibuat.", $action, [], $action->toArray(), $request);

        return redirect()->route('performance.engagement')->with('success', 'Action plan engagement berhasil disimpan.');
    }
}
