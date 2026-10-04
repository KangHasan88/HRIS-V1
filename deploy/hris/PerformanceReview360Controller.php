<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\PerformanceReviewCycle;
use App\Models\PerformanceReviewForm;
use App\Models\PerformanceReviewResponse;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerformanceReview360Controller extends Controller
{
    public function index(): View
    {
        $this->authorizePerformance();

        $cycles = PerformanceReviewCycle::withCount('forms')->latest('period_start')->limit(80)->get();
        $forms = PerformanceReviewForm::with(['cycle', 'employeeProfile', 'manager'])->withCount('responses')->latest()->limit(100)->get();
        $responses = PerformanceReviewResponse::with(['form.employeeProfile', 'reviewerEmployeeProfile'])->latest()->limit(120)->get();

        return view('performance.review-360', [
            'active' => 'performance-review-360',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'users' => User::orderBy('name')->get(),
            'cycles' => $cycles,
            'forms' => $forms,
            'responses' => $responses,
            'summary' => [
                'cycles' => $cycles->count(),
                'active_cycles' => $cycles->where('status', 'active')->count(),
                'forms' => $forms->count(),
                'submitted_responses' => $responses->whereIn('status', ['submitted', 'completed'])->count(),
            ],
        ]);
    }

    public function storeCycle(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'cycle_code' => ['required', 'string', 'max:80', 'unique:performance_review_cycles,cycle_code'],
            'cycle_name' => ['required', 'string', 'max:180'],
            'review_type' => ['required', Rule::in(['360', 'peer', 'manager', 'self', 'probation'])],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'submission_deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed', 'cancelled'])],
            'competency_groups' => ['nullable', 'string', 'max:1200'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $cycle = PerformanceReviewCycle::create([
            ...collect($validated)->except('competency_groups')->all(),
            'competency_groups' => $this->splitList($validated['competency_groups'] ?? null),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('performance_review_cycle.created', "Siklus review {$cycle->cycle_code} dibuat.", $cycle, [], $cycle->toArray(), $request);

        return redirect()->route('performance.review-360')->with('success', 'Siklus review berhasil dibuat.');
    }

    public function storeForm(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'performance_review_cycle_id' => ['required', 'exists:performance_review_cycles,id'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'manager_user_id' => ['nullable', 'exists:users,id'],
            'form_number' => ['required', 'string', 'max:80', 'unique:performance_review_forms,form_number'],
            'review_scope' => ['required', Rule::in(['annual', 'semester', 'quarter', 'probation', 'project'])],
            'status' => ['required', Rule::in(['draft', 'assigned', 'in_review', 'calibrated', 'completed', 'cancelled'])],
            'target_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'summary_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $form = PerformanceReviewForm::create([
            ...$validated,
            'target_score' => $validated['target_score'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('performance_review_form.created', "Form review {$form->form_number} dibuat.", $form, [], $form->toArray(), $request);

        return redirect()->route('performance.review-360')->with('success', 'Form 360 review berhasil dibuat.');
    }

    public function storeResponse(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'performance_review_form_id' => ['required', 'exists:performance_review_forms,id'],
            'reviewer_user_id' => ['nullable', 'exists:users,id'],
            'reviewer_employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'reviewer_type' => ['required', Rule::in(['self', 'manager', 'peer', 'subordinate', 'hr'])],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'competency_scores' => ['nullable', 'string', 'max:1200'],
            'strength_feedback' => ['required', 'string', 'max:2400'],
            'improvement_feedback' => ['nullable', 'string', 'max:2400'],
            'development_suggestion' => ['nullable', 'string', 'max:2400'],
            'status' => ['required', Rule::in(['draft', 'submitted', 'completed', 'returned'])],
        ]);

        $response = PerformanceReviewResponse::create([
            ...collect($validated)->except('competency_scores')->all(),
            'competency_scores' => $this->parseScores($validated['competency_scores'] ?? null),
            'submitted_at' => in_array($validated['status'], ['submitted', 'completed'], true) ? now() : null,
            'created_by' => auth()->id(),
        ]);

        $this->recalculateFinalScore($response->form);

        AuditLogger::record('performance_review_response.created', "Response review {$response->reviewer_type} dibuat.", $response, [], $response->toArray(), $request);

        return redirect()->route('performance.review-360')->with('success', 'Response 360 review berhasil disimpan.');
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

    private function parseScores(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->mapWithKeys(function ($item) {
                [$key, $score] = array_pad(explode(':', $item, 2), 2, 0);
                return [trim($key) => (float) $score];
            })
            ->all();
    }

    private function recalculateFinalScore(PerformanceReviewForm $form): void
    {
        $average = $form->responses()
            ->whereIn('status', ['submitted', 'completed'])
            ->avg('score');

        $form->forceFill([
            'final_score' => $average ? round((float) $average, 2) : 0,
            'status' => $average ? 'in_review' : $form->status,
        ])->save();
    }
}
