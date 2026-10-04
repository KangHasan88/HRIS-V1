<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Competency;
use App\Models\CompetencyRoleRequirement;
use App\Models\JobGrade;
use App\Models\JobPosition;
use App\Models\TrainingCourse;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompetencyMatrixController extends Controller
{
    public function index(): View
    {
        $this->authorizePerformance();

        $competencies = Competency::with('company')->withCount('requirements')->latest()->limit(120)->get();
        $requirements = CompetencyRoleRequirement::with(['competency', 'jobPosition.jobGrade', 'jobGrade', 'trainingCourse'])->latest()->limit(150)->get();

        return view('performance.competency-matrix', [
            'active' => 'performance-competency-matrix',
            'companies' => Company::orderBy('code')->get(),
            'competencies' => $competencies,
            'jobPositions' => JobPosition::with(['company', 'jobGrade'])->orderBy('code')->get(),
            'jobGrades' => JobGrade::orderBy('level')->get(),
            'trainingCourses' => TrainingCourse::orderBy('course_code')->get(),
            'requirements' => $requirements,
            'summary' => [
                'competencies' => $competencies->count(),
                'core' => $competencies->where('is_core', true)->count(),
                'requirements' => $requirements->count(),
                'critical' => $requirements->where('criticality', 'high')->count(),
                'mapped_training' => $requirements->whereNotNull('training_course_id')->count(),
                'avg_target_level' => round((float) $requirements->avg('target_level'), 1),
            ],
        ]);
    }

    public function storeCompetency(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'competency_code' => ['required', 'string', 'max:80', 'unique:competencies,competency_code'],
            'competency_name' => ['required', 'string', 'max:180'],
            'category' => ['required', Rule::in(['core', 'leadership', 'technical', 'behavioral', 'compliance'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'proficiency_descriptors' => ['nullable', 'string', 'max:1600'],
            'is_core' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $competency = Competency::create([
            ...collect($validated)->except(['proficiency_descriptors', 'is_core', 'is_active'])->all(),
            'proficiency_descriptors' => $this->splitList($validated['proficiency_descriptors'] ?? null),
            'is_core' => $request->boolean('is_core'),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('competency.created', "Kompetensi {$competency->competency_code} dibuat.", $competency, [], $competency->toArray(), $request);

        return redirect()->route('performance.competency-matrix')->with('success', 'Kompetensi berhasil dibuat.');
    }

    public function storeRequirement(Request $request): RedirectResponse
    {
        $this->authorizePerformance();

        $validated = $request->validate([
            'competency_id' => ['required', 'exists:competencies,id'],
            'job_position_id' => ['nullable', 'exists:job_positions,id'],
            'job_grade_id' => ['nullable', 'exists:job_grades,id'],
            'training_course_id' => ['nullable', 'exists:training_courses,id'],
            'requirement_number' => ['required', 'string', 'max:80', 'unique:competency_role_requirements,requirement_number'],
            'career_path' => ['nullable', 'string', 'max:180'],
            'required_level' => ['required', 'integer', 'min:1', 'max:5'],
            'target_level' => ['required', 'integer', 'min:1', 'max:5'],
            'weight' => ['required', 'numeric', 'min:1', 'max:100'],
            'criticality' => ['required', Rule::in(['low', 'medium', 'high'])],
            'assessment_method' => ['required', Rule::in(['manager_review', 'assessment', 'certification', 'portfolio', 'interview'])],
            'evidence_examples' => ['nullable', 'string', 'max:1600'],
            'development_action' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
        ]);

        $requirement = CompetencyRoleRequirement::create([
            ...collect($validated)->except('evidence_examples')->all(),
            'evidence_examples' => $this->splitList($validated['evidence_examples'] ?? null),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('competency_role_requirement.created', "Requirement {$requirement->requirement_number} dibuat.", $requirement, [], $requirement->toArray(), $request);

        return redirect()->route('performance.competency-matrix')->with('success', 'Requirement kompetensi berhasil dibuat.');
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
}
