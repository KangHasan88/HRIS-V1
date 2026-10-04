<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EngagementSurvey;
use App\Models\EngagementSurveyResponse;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EssSurveyController extends Controller
{
    public function index(): View
    {
        $employee = $this->currentEmployee();
        $availableSurveys = $this->availableSurveys($employee)->get();
        $responses = $employee
            ? EngagementSurveyResponse::with('survey')
                ->where('employee_profile_id', $employee->id)
                ->latest('submitted_at')
                ->limit(80)
                ->get()
            : collect();

        return view('ess.surveys', [
            'active' => 'ess-surveys',
            'employee' => $employee,
            'surveys' => $availableSurveys,
            'responses' => $responses,
            'summary' => [
                'available' => $availableSurveys->count(),
                'submitted' => $responses->count(),
                'anonymous' => $availableSurveys->where('is_anonymous', true)->count(),
                'avg_enps' => (int) round($responses->avg('enps_score') ?? 0),
            ],
        ]);
    }

    public function storeResponse(Request $request, EngagementSurvey $survey): RedirectResponse
    {
        abort_unless(in_array($survey->status, ['published', 'running'], true), 404);

        $employee = $this->currentEmployee();
        abort_unless($employee, 404, 'Employee profile belum terhubung dengan user login.');
        abort_unless($this->surveyMatchesEmployee($survey, $employee), 403);

        $data = $request->validate([
            'engagement_score' => ['required', 'integer', 'min:0', 'max:100'],
            'enps_score' => ['required', 'integer', 'min:0', 'max:10'],
            'sentiment' => ['required', Rule::in(['positive', 'neutral', 'negative', 'mixed'])],
            'dimension_scores' => ['nullable', 'string', 'max:700'],
            'comment' => ['nullable', 'string', 'max:1500'],
        ]);

        $response = EngagementSurveyResponse::create([
            'engagement_survey_id' => $survey->id,
            'employee_profile_id' => $survey->is_anonymous ? null : $employee->id,
            'response_number' => 'ESS-SVY-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
            'engagement_score' => $data['engagement_score'],
            'enps_score' => $data['enps_score'],
            'sentiment' => $data['sentiment'],
            'dimension_scores' => collect(explode(',', (string) ($data['dimension_scores'] ?? '')))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values()
                ->all(),
            'comment' => $data['comment'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by' => $survey->is_anonymous ? null : auth()->id(),
        ]);

        AuditLogger::record(
            'ess_survey_response.submitted',
            $survey->is_anonymous
                ? "Response anonim untuk survey {$survey->survey_number} dikirim dari ESS."
                : "Response survey {$survey->survey_number} dikirim oleh {$employee->employee_number}.",
            $response,
            [],
            [
                'survey_id' => $survey->id,
                'survey_number' => $survey->survey_number,
                'anonymous' => $survey->is_anonymous,
                'status' => $response->status,
            ],
            $request,
        );

        return redirect()->route('ess.surveys')->with('success', 'Response survey berhasil dikirim.');
    }

    private function availableSurveys(?EmployeeProfile $employee)
    {
        return EngagementSurvey::withCount('responses')
            ->whereIn('status', ['published', 'running'])
            ->where(function ($query) {
                $query->whereNull('start_date')->orWhereDate('start_date', '<=', now()->toDateString());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
            })
            ->when($employee, fn ($query) => $query->where(function ($scope) use ($employee) {
                $scope->where('audience_scope', 'all_employee')
                    ->orWhere(function ($company) use ($employee) {
                        $company->where('audience_scope', 'company')->where('company_id', $employee->company_id);
                    })
                    ->orWhere(function ($branch) use ($employee) {
                        $branch->where('audience_scope', 'branch')->where('branch_id', $employee->branch_id);
                    })
                    ->orWhere(function ($department) use ($employee) {
                        $department->where('audience_scope', 'department')->where('notes', 'like', '%'.$employee->department.'%');
                    });
            }))
            ->latest('start_date')
            ->latest();
    }

    private function surveyMatchesEmployee(EngagementSurvey $survey, EmployeeProfile $employee): bool
    {
        if ($survey->audience_scope === 'all_employee') {
            return true;
        }

        if ($survey->audience_scope === 'company') {
            return (int) $survey->company_id === (int) $employee->company_id;
        }

        if ($survey->audience_scope === 'branch') {
            return (int) $survey->branch_id === (int) $employee->branch_id;
        }

        if ($survey->audience_scope === 'department') {
            return $employee->department && str_contains(strtolower((string) $survey->notes), strtolower($employee->department));
        }

        return false;
    }

    private function currentEmployee(): ?EmployeeProfile
    {
        return EmployeeProfile::with(['company', 'branch'])
            ->where('user_id', auth()->id())
            ->orWhere('work_email', auth()->user()?->email)
            ->first();
    }
}
