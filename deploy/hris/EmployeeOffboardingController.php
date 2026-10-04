<?php

namespace App\Http\Controllers;

use App\Models\EmployeeClearanceItem;
use App\Models\EmployeeOffboardingCase;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeOffboardingController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        return view('employee-offboarding', [
            'active' => 'employee-offboarding',
            'employees' => EmployeeProfile::with(['company', 'branch'])->orderBy('full_name')->get(),
            'cases' => EmployeeOffboardingCase::with(['employeeProfile.company', 'employeeProfile.branch', 'clearanceItems'])
                ->latest('last_working_date')
                ->latest()
                ->limit(120)
                ->get(),
            'summary' => [
                'initiated' => EmployeeOffboardingCase::where('status', 'initiated')->count(),
                'clearance' => EmployeeOffboardingCase::where('status', 'clearance')->count(),
                'settlement' => EmployeeOffboardingCase::where('status', 'settlement')->count(),
                'closed' => EmployeeOffboardingCase::where('status', 'closed')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'case_number' => ['required', 'string', 'max:80', 'unique:employee_offboarding_cases,case_number'],
            'reason_type' => ['required', Rule::in(['resign', 'termination', 'retirement', 'end_contract', 'mutual_agreement'])],
            'notice_date' => ['nullable', 'date'],
            'last_working_date' => ['required', 'date'],
            'exit_interview_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['initiated', 'clearance', 'settlement', 'closed', 'cancelled'])],
            'handled_by' => ['nullable', 'string', 'max:160'],
            'approved_by' => ['nullable', 'string', 'max:160'],
            'final_settlement_amount' => ['nullable', 'numeric', 'min:0'],
            'final_settlement_status' => ['required', Rule::in(['pending', 'calculated', 'approved', 'paid'])],
            'reason_note' => ['nullable', 'string', 'max:1500'],
            'exit_interview_summary' => ['nullable', 'string', 'max:1500'],
            'handover_note' => ['nullable', 'string', 'max:1500'],
            'clearance_items' => ['required', 'array', 'min:1'],
            'clearance_items.*' => ['required', 'string', 'max:180'],
        ]);

        $employee = EmployeeProfile::with(['company', 'branch'])->findOrFail($data['employee_profile_id']);

        $case = EmployeeOffboardingCase::create([
            ...collect($data)->except('clearance_items')->all(),
            'final_settlement_amount' => $data['final_settlement_amount'] ?? 0,
            'employee_snapshot' => [
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->full_name,
                'company' => $employee->company?->code,
                'branch' => $employee->branch?->code,
                'position' => $employee->position,
                'department' => $employee->department,
                'grade' => $employee->grade,
                'cost_center' => $employee->cost_center,
            ],
            'created_by' => auth()->id(),
        ]);

        foreach ($data['clearance_items'] as $index => $itemName) {
            $case->clearanceItems()->create([
                'category' => $this->categoryFor($itemName),
                'item_name' => $itemName,
                'owner_role' => $index % 2 === 0 ? 'HR Admin' : 'Finance/IT',
                'due_date' => $data['last_working_date'],
                'status' => 'open',
            ]);
        }

        if (in_array($case->status, ['closed'], true)) {
            $employee->update(['is_active' => false, 'contract_end_date' => $case->last_working_date]);
        }

        AuditLogger::record('employee_offboarding_case.created', "Offboarding {$case->case_number} dibuat untuk {$employee->employee_number}.", $case, [], $case->load('clearanceItems')->toArray(), $request);

        return redirect()->route('hris.employee-offboarding')->with('success', 'Case offboarding berhasil dibuat.');
    }

    public function updateClearance(Request $request, EmployeeClearanceItem $item): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'completed', 'waived', 'blocked'])],
            'completed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $before = $item->only(['status', 'completed_at', 'notes']);
        $item->update([
            ...$data,
            'completed_at' => in_array($data['status'], ['completed', 'waived'], true) ? ($data['completed_at'] ?? now()->toDateString()) : ($data['completed_at'] ?? null),
        ]);

        AuditLogger::record('employee_clearance_item.updated', "Clearance {$item->item_name} diupdate.", $item, $before, $item->fresh()->toArray(), $request);

        return redirect()->route('hris.employee-offboarding')->with('success', 'Clearance item berhasil diperbarui.');
    }

    private function categoryFor(string $itemName): string
    {
        $name = strtolower($itemName);

        return match (true) {
            str_contains($name, 'aset') || str_contains($name, 'laptop') => 'asset',
            str_contains($name, 'akses') || str_contains($name, 'akun') => 'access',
            str_contains($name, 'payroll') || str_contains($name, 'settlement') => 'payroll',
            str_contains($name, 'dokumen') => 'document',
            default => 'general',
        };
    }
}
