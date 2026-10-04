<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeOnboardingTask;
use App\Models\EmployeeOnboardingTemplate;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeOnboardingController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        return view('employee-onboarding', [
            'active' => 'employee-onboarding',
            'employees' => EmployeeProfile::with(['company', 'branch'])->orderBy('full_name')->get(),
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::with('company')->orderBy('code')->get(),
            'templates' => EmployeeOnboardingTemplate::with(['company', 'branch'])->latest()->limit(100)->get(),
            'tasks' => EmployeeOnboardingTask::with(['employeeProfile.company', 'employeeProfile.branch', 'template'])->orderBy('due_date')->latest()->limit(150)->get(),
            'summary' => [
                'templates' => EmployeeOnboardingTemplate::where('is_active', true)->count(),
                'open' => EmployeeOnboardingTask::where('status', 'open')->count(),
                'in_progress' => EmployeeOnboardingTask::where('status', 'in_progress')->count(),
                'completed' => EmployeeOnboardingTask::where('status', 'completed')->count(),
            ],
        ]);
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:80', 'unique:employee_onboarding_templates,code'],
            'name' => ['required', 'string', 'max:180'],
            'role_scope' => ['nullable', 'string', 'max:120'],
            'department_scope' => ['nullable', 'string', 'max:120'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'task_names' => ['required', 'array', 'min:1'],
            'task_names.*' => ['required', 'string', 'max:180'],
        ]);

        $template = EmployeeOnboardingTemplate::create([
            ...collect($data)->except('task_names')->all(),
            'task_blueprint' => collect($data['task_names'])->values()->map(fn (string $name, int $index) => [
                'task_code' => strtoupper($data['code']) . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'task_name' => $name,
                'category' => 'onboarding',
                'sort_order' => $index + 1,
            ])->all(),
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('employee_onboarding_template.created', "Template onboarding {$template->code} dibuat.", $template, [], $template->toArray(), $request);

        return redirect()->route('hris.employee-onboarding')->with('success', 'Template onboarding berhasil dibuat.');
    }

    public function assign(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'employee_onboarding_template_id' => ['required', 'exists:employee_onboarding_templates,id'],
            'base_due_date' => ['required', 'date'],
            'owner_role' => ['nullable', 'string', 'max:120'],
            'owner_name' => ['nullable', 'string', 'max:160'],
        ]);

        $employee = EmployeeProfile::with(['company', 'branch'])->findOrFail($data['employee_profile_id']);
        $template = EmployeeOnboardingTemplate::findOrFail($data['employee_onboarding_template_id']);
        $created = collect($template->task_blueprint ?? [])->map(function (array $blueprint) use ($data, $employee, $template) {
            return EmployeeOnboardingTask::firstOrCreate(
                [
                    'employee_profile_id' => $employee->id,
                    'task_code' => $blueprint['task_code'],
                ],
                [
                    'employee_onboarding_template_id' => $template->id,
                    'task_name' => $blueprint['task_name'],
                    'category' => $blueprint['category'] ?? 'onboarding',
                    'owner_role' => $data['owner_role'] ?? 'HR Admin',
                    'owner_name' => $data['owner_name'] ?? null,
                    'due_date' => $data['base_due_date'],
                    'status' => 'open',
                    'sort_order' => $blueprint['sort_order'] ?? 1,
                    'instruction' => $blueprint['instruction'] ?? null,
                    'assignment_snapshot' => [
                        'employee_number' => $employee->employee_number,
                        'full_name' => $employee->full_name,
                        'company' => $employee->company?->code,
                        'branch' => $employee->branch?->code,
                        'position' => $employee->position,
                        'department' => $employee->department,
                    ],
                    'created_by' => auth()->id(),
                ]
            );
        });

        AuditLogger::record('employee_onboarding_task.assigned', "{$created->count()} task onboarding dibuat untuk {$employee->employee_number}.", $employee, [], ['template' => $template->code, 'tasks' => $created->pluck('task_code')->all()], $request);

        return redirect()->route('hris.employee-onboarding')->with('success', 'Task onboarding berhasil digenerate ke karyawan.');
    }

    public function updateTask(Request $request, EmployeeOnboardingTask $task): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'completed', 'blocked', 'cancelled'])],
            'completed_at' => ['nullable', 'date'],
            'completion_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $before = $task->only(['status', 'completed_at', 'completion_note']);
        $task->update([
            ...$data,
            'completed_at' => $data['status'] === 'completed' ? ($data['completed_at'] ?? now()->toDateString()) : ($data['completed_at'] ?? null),
        ]);

        AuditLogger::record('employee_onboarding_task.updated', "Task onboarding {$task->task_code} diupdate.", $task, $before, $task->fresh()->toArray(), $request);

        return redirect()->route('hris.employee-onboarding')->with('success', 'Status task onboarding berhasil diperbarui.');
    }
}
