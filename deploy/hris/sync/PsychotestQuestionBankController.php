<?php

namespace App\Http\Controllers;

use App\Models\PsychotestBatteryTemplate;
use App\Models\PsychotestDimension;
use App\Models\PsychotestPackage;
use App\Models\PsychotestQuestion;
use App\Models\PsychotestQuestionCategory;
use App\Models\PsychotestQuestionOption;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PsychotestQuestionBankController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        $categories = PsychotestQuestionCategory::withCount(['dimensions'])->latest()->limit(120)->get();
        $dimensions = PsychotestDimension::with('category')->withCount('questions')->latest()->limit(160)->get();
        $questions = PsychotestQuestion::with(['dimension.category', 'options'])->latest()->limit(180)->get();
        $templates = PsychotestBatteryTemplate::with('package.provider')->latest()->limit(120)->get();

        return view('recruitment-psychotest-question-bank', [
            'active' => 'recruitment-psychotest-question-bank',
            'categories' => $categories,
            'dimensions' => $dimensions,
            'questions' => $questions,
            'templates' => $templates,
            'packages' => PsychotestPackage::with('provider')->whereIn('status', ['active', 'testing'])->orderBy('package_code')->get(),
            'summary' => [
                'categories' => $categories->count(),
                'dimensions' => $dimensions->count(),
                'questions' => $questions->count(),
                'active_questions' => $questions->where('status', 'active')->count(),
                'templates' => $templates->count(),
            ],
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'category_code' => ['required', 'string', 'max:100', 'unique:psychotest_question_categories,category_code'],
            'category_name' => ['required', 'string', 'max:255'],
            'assessment_domain' => ['required', Rule::in(['personality', 'cognitive', 'integrity', 'leadership', 'technical', 'culture_fit'])],
            'owner_name' => ['nullable', 'string', 'max:160'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1200'],
        ]);

        $category = PsychotestQuestionCategory::create([
            ...collect($data)->except('is_active')->all(),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('psychotest_question_category.created', "Kategori soal {$category->category_code} dibuat.", $category, [], $category->toArray(), $request);

        return redirect()->route('recruitment.psychotest-question-bank')->with('success', 'Kategori bank soal berhasil disimpan.');
    }

    public function storeDimension(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'psychotest_question_category_id' => ['required', 'exists:psychotest_question_categories,id'],
            'dimension_code' => ['required', 'string', 'max:100', 'unique:psychotest_dimensions,dimension_code'],
            'dimension_name' => ['required', 'string', 'max:255'],
            'scoring_direction' => ['required', Rule::in(['positive', 'reverse', 'neutral', 'weighted'])],
            'weight_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'interpretation_note' => ['nullable', 'string', 'max:1200'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $dimension = PsychotestDimension::create([
            ...collect($data)->except('is_active')->all(),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('psychotest_dimension.created', "Dimensi soal {$dimension->dimension_code} dibuat.", $dimension, [], $dimension->toArray(), $request);

        return redirect()->route('recruitment.psychotest-question-bank')->with('success', 'Dimensi psikotest berhasil disimpan.');
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'psychotest_dimension_id' => ['required', 'exists:psychotest_dimensions,id'],
            'question_code' => ['required', 'string', 'max:100', 'unique:psychotest_questions,question_code'],
            'question_type' => ['required', Rule::in(['single_choice', 'likert', 'true_false', 'priority_order', 'case_study'])],
            'question_text' => ['required', 'string', 'max:2000'],
            'instruction' => ['nullable', 'string', 'max:1000'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:10', 'max:7200'],
            'difficulty_level' => ['required', Rule::in(['easy', 'normal', 'hard', 'expert'])],
            'version' => ['required', 'integer', 'min:1', 'max:999'],
            'status' => ['required', Rule::in(['draft', 'active', 'retired'])],
            'scoring_key' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1200'],
            'option_codes' => ['nullable', 'array'],
            'option_codes.*' => ['nullable', 'string', 'max:100'],
            'option_texts' => ['nullable', 'array'],
            'option_texts.*' => ['nullable', 'string', 'max:1000'],
            'option_scores' => ['nullable', 'array'],
            'option_scores.*' => ['nullable', 'numeric', 'min:-100', 'max:100'],
        ]);

        $question = PsychotestQuestion::create([
            ...collect($data)->except(['scoring_key', 'option_codes', 'option_texts', 'option_scores'])->all(),
            'scoring_key' => collect(explode(',', (string) ($data['scoring_key'] ?? '')))->map(fn ($item) => trim($item))->filter()->values()->all(),
            'created_by' => auth()->id(),
        ]);

        $codes = $data['option_codes'] ?? [];
        $texts = $data['option_texts'] ?? [];
        $scores = $data['option_scores'] ?? [];
        foreach ($texts as $index => $text) {
            if (! $text) {
                continue;
            }

            PsychotestQuestionOption::create([
                'psychotest_question_id' => $question->id,
                'option_code' => $codes[$index] ?: 'OPT-'.($index + 1),
                'option_text' => $text,
                'score_value' => $scores[$index] ?? 0,
                'sort_order' => $index + 1,
                'is_correct' => false,
            ]);
        }

        AuditLogger::record('psychotest_question.created', "Soal psikotest {$question->question_code} dibuat.", $question, [], $question->load('options')->toArray(), $request);

        return redirect()->route('recruitment.psychotest-question-bank')->with('success', 'Soal psikotest berhasil disimpan.');
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'psychotest_package_id' => ['required', 'exists:psychotest_packages,id'],
            'template_code' => ['required', 'string', 'max:100', 'unique:psychotest_battery_templates,template_code'],
            'template_name' => ['required', 'string', 'max:255'],
            'target_role_level' => ['required', Rule::in(['all_level', 'operator', 'staff', 'supervisor', 'manager', 'executive'])],
            'total_duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'section_config' => ['nullable', 'string', 'max:1000'],
            'version' => ['required', 'integer', 'min:1', 'max:999'],
            'status' => ['required', Rule::in(['draft', 'active', 'retired'])],
            'instruction' => ['nullable', 'string', 'max:1500'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $template = PsychotestBatteryTemplate::create([
            ...collect($data)->except('section_config')->all(),
            'section_config' => collect(explode(',', (string) ($data['section_config'] ?? '')))->map(fn ($item) => trim($item))->filter()->values()->all(),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('psychotest_battery_template.created', "Template battery {$template->template_code} dibuat.", $template, [], $template->toArray(), $request);

        return redirect()->route('recruitment.psychotest-question-bank')->with('success', 'Template battery berhasil disimpan.');
    }
}
