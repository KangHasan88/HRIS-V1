<?php

namespace App\Http\Controllers;

use App\Models\EmployeeOnboardingTask;
use App\Models\EmployeeProfile;
use App\Models\RecruitmentEmployeeConversion;
use App\Models\RecruitmentOnboardingJourney;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecruitmentOnboardingJourneyController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        $journeys = RecruitmentOnboardingJourney::with(['employeeProfile.company', 'employeeProfile.branch', 'conversion'])
            ->latest()
            ->limit(200)
            ->get();

        return view('recruitment-onboarding-journeys', [
            'active' => 'recruitment-onboarding-journeys',
            'employees' => EmployeeProfile::with(['company', 'branch'])
                ->where('is_active', true)
                ->latest()
                ->limit(200)
                ->get(),
            'conversions' => RecruitmentEmployeeConversion::with('candidate')->where('conversion_status', 'converted')->latest()->limit(100)->get(),
            'journeys' => $journeys,
            'summary' => [
                'total' => $journeys->count(),
                'planned' => $journeys->where('journey_status', 'planned')->count(),
                'active' => $journeys->where('journey_status', 'active')->count(),
                'completed' => $journeys->where('journey_status', 'completed')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'recruitment_employee_conversion_id' => ['nullable', 'exists:recruitment_employee_conversions,id'],
            'journey_number' => ['required', 'string', 'max:80', 'unique:recruitment_onboarding_journeys,journey_number'],
            'buddy_name' => ['required', 'string', 'max:160'],
            'buddy_role' => ['nullable', 'string', 'max:120'],
            'buddy_contact' => ['nullable', 'string', 'max:160'],
            'start_date' => ['nullable', 'date'],
            'probation_end_date' => ['nullable', 'date'],
            'probation_reviewer' => ['nullable', 'string', 'max:160'],
            'journey_status' => ['required', Rule::in(['planned', 'active', 'completed', 'blocked', 'cancelled'])],
            'checklist_items' => ['required', 'array', 'min:1'],
            'checklist_items.*' => ['required', 'string', 'max:180'],
            'probation_goals' => ['nullable', 'array'],
            'probation_goals.*' => ['nullable', 'string', 'max:180'],
            'welcome_agenda' => ['nullable', 'array'],
            'welcome_agenda.*' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:1600'],
        ]);

        $employee = EmployeeProfile::findOrFail($data['employee_profile_id']);
        $checklist = collect($data['checklist_items'])->filter()->values();

        $journey = RecruitmentOnboardingJourney::create([
            ...collect($data)->except(['checklist_items', 'probation_goals', 'welcome_agenda'])->all(),
            'checklist_snapshot' => $checklist->map(fn (string $item, int $index) => [
                'code' => strtoupper($data['journey_number']) . '-TASK-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'item' => $item,
                'status' => 'open',
            ])->all(),
            'probation_plan' => collect($data['probation_goals'] ?? [])->filter()->values()->map(fn (string $goal) => ['goal' => $goal, 'status' => 'open'])->all(),
            'welcome_schedule' => collect($data['welcome_agenda'] ?? [])->filter()->values()->map(fn (string $agenda) => ['agenda' => $agenda, 'status' => 'planned'])->all(),
            'created_by' => auth()->id(),
        ]);

        foreach ($journey->checklist_snapshot ?? [] as $index => $item) {
            EmployeeOnboardingTask::firstOrCreate(
                ['employee_profile_id' => $employee->id, 'task_code' => $item['code']],
                [
                    'task_name' => $item['item'],
                    'category' => 'welcome_journey',
                    'owner_role' => 'Buddy',
                    'owner_name' => $data['buddy_name'],
                    'due_date' => $data['start_date'] ?? now()->toDateString(),
                    'status' => 'open',
                    'sort_order' => $index + 1,
                    'instruction' => 'Generated dari Welcome Journey recruitment onboarding.',
                    'assignment_snapshot' => [
                        'employee_number' => $employee->employee_number,
                        'full_name' => $employee->full_name,
                        'buddy' => $data['buddy_name'],
                        'journey_number' => $journey->journey_number,
                    ],
                    'created_by' => auth()->id(),
                ]
            );
        }

        AuditLogger::record('recruitment_onboarding_journey.created', "Welcome journey {$journey->journey_number} dibuat.", $journey, [], $journey->toArray(), $request);

        return redirect()->route('recruitment.onboarding-journeys')->with('success', 'Welcome journey onboarding berhasil dibuat.');
    }
}
