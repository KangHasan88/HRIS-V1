<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeMovement;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeMovementController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        return view('employee-movements', [
            'active' => 'employee-movements',
            'employees' => EmployeeProfile::with(['company', 'branch'])->orderBy('full_name')->get(),
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::with('company')->orderBy('code')->get(),
            'movements' => EmployeeMovement::with(['employeeProfile.company', 'employeeProfile.branch', 'fromCompany', 'toCompany', 'fromBranch', 'toBranch'])
                ->latest('effective_date')
                ->latest()
                ->limit(150)
                ->get(),
            'summary' => [
                'requested' => EmployeeMovement::where('status', 'requested')->count(),
                'approved' => EmployeeMovement::where('status', 'approved')->count(),
                'effective' => EmployeeMovement::where('status', 'effective')->count(),
                'transfer' => EmployeeMovement::where('movement_type', 'transfer')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'movement_number' => ['required', 'string', 'max:80', 'unique:employee_movements,movement_number'],
            'movement_type' => ['required', Rule::in(['mutation', 'promotion', 'demotion', 'transfer'])],
            'request_date' => ['nullable', 'date'],
            'effective_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['draft', 'requested', 'approved', 'rejected', 'effective', 'cancelled'])],
            'to_company_id' => ['nullable', 'exists:companies,id'],
            'to_branch_id' => ['nullable', 'exists:branches,id'],
            'to_position' => ['nullable', 'string', 'max:160'],
            'to_department' => ['nullable', 'string', 'max:160'],
            'to_grade' => ['nullable', 'string', 'max:80'],
            'to_cost_center' => ['nullable', 'string', 'max:120'],
            'reason' => ['nullable', 'string', 'max:1200'],
            'approved_by' => ['nullable', 'string', 'max:160'],
            'approval_notes' => ['nullable', 'string', 'max:1200'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $employee = EmployeeProfile::with(['company', 'branch'])->findOrFail($data['employee_profile_id']);
        $beforeSnapshot = $this->snapshot($employee);
        $afterSnapshot = [
            ...$beforeSnapshot,
            'company_id' => $data['to_company_id'] ?? $employee->company_id,
            'company_code' => Company::find($data['to_company_id'] ?? $employee->company_id)?->code,
            'branch_id' => $data['to_branch_id'] ?? $employee->branch_id,
            'branch_code' => Branch::find($data['to_branch_id'] ?? $employee->branch_id)?->code,
            'position' => $data['to_position'] ?: $employee->position,
            'department' => $data['to_department'] ?: $employee->department,
            'grade' => $data['to_grade'] ?: $employee->grade,
            'cost_center' => $data['to_cost_center'] ?: $employee->cost_center,
        ];

        $movement = EmployeeMovement::create([
            ...$data,
            'request_date' => $data['request_date'] ?? now()->toDateString(),
            'from_company_id' => $employee->company_id,
            'from_branch_id' => $employee->branch_id,
            'from_position' => $employee->position,
            'from_department' => $employee->department,
            'from_grade' => $employee->grade,
            'from_cost_center' => $employee->cost_center,
            'to_company_id' => $data['to_company_id'] ?? $employee->company_id,
            'to_branch_id' => $data['to_branch_id'] ?? $employee->branch_id,
            'to_position' => $data['to_position'] ?: $employee->position,
            'to_department' => $data['to_department'] ?: $employee->department,
            'to_grade' => $data['to_grade'] ?: $employee->grade,
            'to_cost_center' => $data['to_cost_center'] ?: $employee->cost_center,
            'before_snapshot' => $beforeSnapshot,
            'after_snapshot' => $afterSnapshot,
            'created_by' => auth()->id(),
        ]);

        if (in_array($movement->status, ['approved', 'effective'], true)) {
            $employee->update([
                'company_id' => $movement->to_company_id,
                'branch_id' => $movement->to_branch_id,
                'position' => $movement->to_position,
                'department' => $movement->to_department,
                'grade' => $movement->to_grade,
                'cost_center' => $movement->to_cost_center,
            ]);
        }

        AuditLogger::record('employee_movement.created', "Movement {$movement->movement_number} dibuat untuk {$employee->employee_number}.", $movement, $beforeSnapshot, $movement->toArray(), $request);

        return redirect()->route('hris.employee-movements')->with('success', 'Workflow mutasi karyawan berhasil dicatat.');
    }

    private function snapshot(EmployeeProfile $employee): array
    {
        return [
            'employee_number' => $employee->employee_number,
            'full_name' => $employee->full_name,
            'company_id' => $employee->company_id,
            'company_code' => $employee->company?->code,
            'branch_id' => $employee->branch_id,
            'branch_code' => $employee->branch?->code,
            'position' => $employee->position,
            'department' => $employee->department,
            'grade' => $employee->grade,
            'cost_center' => $employee->cost_center,
        ];
    }
}
