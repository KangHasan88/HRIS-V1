<?php

namespace App\Http\Controllers;

use App\Models\EmployeeContract;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeContractController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $contracts = EmployeeContract::with(['employeeProfile.company', 'employeeProfile.branch', 'parentContract'])
            ->latest('start_date')
            ->limit(120)
            ->get();

        return view('employee-contracts', [
            'active' => 'employee-contracts',
            'employees' => EmployeeProfile::with(['company', 'branch'])->orderBy('full_name')->get(),
            'contractOptions' => EmployeeContract::with('employeeProfile')->orderByDesc('start_date')->get(),
            'contracts' => $contracts,
            'summary' => [
                'active' => EmployeeContract::where('status', 'active')->count(),
                'pkwt' => EmployeeContract::where('contract_type', 'pkwt')->count(),
                'pkwtt' => EmployeeContract::where('contract_type', 'pkwtt')->count(),
                'renewal' => EmployeeContract::where('renewal_sequence', '>', 0)->count(),
                'due_reminder' => EmployeeContract::whereNotNull('reminder_date')->whereDate('reminder_date', '<=', now()->addDays(30))->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'parent_contract_id' => ['nullable', 'exists:employee_contracts,id'],
            'contract_number' => ['required', 'string', 'max:80', 'unique:employee_contracts,contract_number'],
            'contract_type' => ['required', Rule::in(['pkwt', 'pkwtt', 'internship', 'freelance'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'signed_at' => ['nullable', 'date'],
            'reminder_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['draft', 'active', 'expiring', 'ended', 'terminated'])],
            'document_reference' => ['nullable', 'string', 'max:160'],
            'approved_by' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = EmployeeProfile::with(['company', 'branch'])->findOrFail($data['employee_profile_id']);
        $parent = filled($data['parent_contract_id'] ?? null)
            ? EmployeeContract::find($data['parent_contract_id'])
            : null;

        $contract = EmployeeContract::create([
            ...$data,
            'renewal_sequence' => $parent ? $parent->renewals()->count() + 1 : 0,
            'contract_snapshot' => [
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->full_name,
                'company' => $employee->company?->code,
                'branch' => $employee->branch?->code,
                'position' => $employee->position,
                'department' => $employee->department,
            ],
            'created_by' => auth()->id(),
        ]);

        $employee->employment_status = $data['contract_type'] === 'pkwtt' ? 'permanent' : 'contract';
        $employee->contract_end_date = $data['end_date'] ?? null;
        $employee->is_active = $data['status'] !== 'terminated';
        $employee->save();

        AuditLogger::record(
            'employee_contract.created',
            "Kontrak {$contract->contract_number} untuk {$employee->employee_number} dibuat.",
            $contract,
            [],
            $contract->toArray(),
            $request
        );

        return redirect()->route('hris.employee-contracts')->with('success', 'Kontrak kerja berhasil disimpan.');
    }
}
