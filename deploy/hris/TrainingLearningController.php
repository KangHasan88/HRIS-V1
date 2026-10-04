<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrainingLearningController extends Controller
{
    public function index(): View
    {
        $this->authorizeLearning();

        $courses = TrainingCourse::with(['company', 'branch'])
            ->withCount('enrollments')
            ->latest('start_date')
            ->latest()
            ->limit(120)
            ->get();

        $enrollments = TrainingEnrollment::with(['course', 'employeeProfile.branch'])
            ->latest()
            ->limit(150)
            ->get();

        return view('performance.training-learning', [
            'active' => 'training-learning',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'courses' => $courses,
            'enrollments' => $enrollments,
            'summary' => [
                'courses' => $courses->count(),
                'published' => $courses->where('status', 'published')->count(),
                'enrolled' => $enrollments->where('enrollment_status', 'enrolled')->count(),
                'certified' => $enrollments->whereNotNull('certificate_number')->count(),
            ],
        ]);
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $this->authorizeLearning();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'course_code' => ['required', 'string', 'max:80', 'unique:training_courses,course_code'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['technical', 'leadership', 'compliance', 'hse', 'soft_skill', 'certification'])],
            'delivery_mode' => ['required', Rule::in(['classroom', 'online', 'hybrid', 'on_the_job'])],
            'provider' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'capacity' => ['required', 'integer', 'min:0', 'max:10000'],
            'status' => ['required', Rule::in(['draft', 'published', 'closed', 'cancelled'])],
            'description' => ['nullable', 'string', 'max:1500'],
        ]);

        $course = TrainingCourse::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'training_course.created',
            "Training {$course->course_code} dibuat.",
            $course,
            [],
            $course->toArray(),
            $request,
        );

        return redirect()->route('performance.training-learning')->with('success', 'Katalog training berhasil dibuat.');
    }

    public function storeEnrollment(Request $request): RedirectResponse
    {
        $this->authorizeLearning();

        $validated = $request->validate([
            'training_course_id' => ['required', 'exists:training_courses,id'],
            'employee_profile_id' => [
                'required',
                'exists:employee_profiles,id',
                Rule::unique('training_enrollments')->where(fn ($query) => $query->where('training_course_id', $request->input('training_course_id'))),
            ],
            'enrollment_number' => ['required', 'string', 'max:80', 'unique:training_enrollments,enrollment_number'],
            'enrollment_status' => ['required', Rule::in(['nominated', 'enrolled', 'waitlisted', 'completed', 'cancelled'])],
            'attendance_status' => ['required', Rule::in(['not_started', 'present', 'absent', 'partial', 'excused'])],
            'attended_at' => ['nullable', 'date'],
            'certificate_number' => ['nullable', 'string', 'max:120'],
            'certificate_issued_at' => ['nullable', 'date'],
            'certificate_file_reference' => ['nullable', 'string', 'max:255'],
            'evaluation_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'evaluation_feedback' => ['nullable', 'string', 'max:1500'],
            'manager_notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $enrollment = TrainingEnrollment::create([
            ...$validated,
            'evaluation_score' => $validated['evaluation_score'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'training_enrollment.created',
            "Enrollment {$enrollment->enrollment_number} dibuat.",
            $enrollment,
            [],
            $enrollment->toArray(),
            $request,
        );

        return redirect()->route('performance.training-learning')->with('success', 'Enrollment training berhasil disimpan.');
    }

    private function authorizeLearning(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage recruitment') || $user?->hasPermission('manage modules'), 403);
    }
}
