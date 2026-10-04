<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompensationAccessPolicy;
use App\Models\EmployeeProfile;
use App\Models\EmployeeSalaryAssignment;
use App\Models\JobGrade;
use App\Models\JobPosition;
use App\Models\PayrollSalaryBand;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollSalaryStructureController extends Controller
{
    public function index(): View
    {
        $this->authorizePayroll();

        $bands = PayrollSalaryBand::with(['company', 'jobGrade'])
            ->orderBy('level')
            ->orderBy('band_code')
            ->limit(150)
            ->get();
        $assignments = EmployeeSalaryAssignment::with(['employeeProfile', 'salaryBand', 'jobPosition'])
            ->latest()
            ->limit(120)
            ->get();
        $policies = CompensationAccessPolicy::latest()->limit(80)->get();

        return view('payroll.salary-structures', [
            'active' => 'payroll-salary-structures',
            'companies' => Company::orderBy('code')->get(),
            'jobGrades' => JobGrade::with('company')->orderBy('level')->get(),
            'jobPositions' => JobPosition::with(['company', 'jobGrade'])->orderBy('code')->get(),
            'employees' => EmployeeProfile::with('company')->orderBy('employee_number')->limit(250)->get(),
            'bands' => $bands,
            'assignments' => $assignments,
            'policies' => $policies,
            'summary' => [
                'bands' => $bands->count(),
                'assignments' => $assignments->count(),
                'policies' => $policies->count(),
                'restricted' => $assignments->where('confidentiality_level', 'restricted')->count(),
            ],
        ]);
    }

    public function storeBand(Request $request): RedirectResponse
    {
        $this->authorizePayroll();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'job_grade_id' => ['nullable', 'exists:job_grades,id'],
            'band_code' => ['required', 'string', 'max:80', 'unique:payroll_salary_bands,band_code'],
            'band_name' => ['required', 'string', 'max:180'],
            'level' => ['required', 'integer', 'min:1', 'max:200'],
            'minimum_salary' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'midpoint_salary' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'maximum_salary' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'currency' => ['required', 'string', 'max:10'],
            'effective_start' => ['nullable', 'date'],
            'effective_end' => ['nullable', 'date', 'after_or_equal:effective_start'],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive', 'archived'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $band = PayrollSalaryBand::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('payroll_salary_band.created', "Salary band {$band->band_code} dibuat.", $band, [], $band->toArray(), $request);

        return redirect()->route('payroll.salary-structures')->with('success', 'Salary band berhasil disimpan.');
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $this->authorizePayroll();

        $validated = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'payroll_salary_band_id' => ['nullable', 'exists:payroll_salary_bands,id'],
            'job_position_id' => ['nullable', 'exists:job_positions,id'],
            'base_salary' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'fixed_allowance' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'variable_allowance' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'payroll_frequency' => ['required', Rule::in(['monthly', 'daily', 'weekly'])],
            'effective_start' => ['nullable', 'date'],
            'effective_end' => ['nullable', 'date', 'after_or_equal:effective_start'],
            'approval_status' => ['required', Rule::in(['draft', 'pending_approval', 'approved', 'rejected'])],
            'confidentiality_level' => ['required', Rule::in(['restricted', 'payroll_only', 'hr_payroll', 'executive'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $assignment = EmployeeSalaryAssignment::create([
            ...$validated,
            'fixed_allowance' => $validated['fixed_allowance'] ?? 0,
            'variable_allowance' => $validated['variable_allowance'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('employee_salary_assignment.created', 'Assignment kompensasi karyawan dibuat.', $assignment, [], $assignment->toArray(), $request);

        return redirect()->route('payroll.salary-structures')->with('success', 'Assignment kompensasi berhasil disimpan.');
    }

    public function storePolicy(Request $request): RedirectResponse
    {
        $this->authorizePayroll();

        $validated = $request->validate([
            'policy_name' => ['required', 'string', 'max:180'],
            'role_scope' => ['required', 'string', 'max:120'],
            'access_level' => ['required', Rule::in(['masked', 'summary', 'detail', 'export'])],
            'can_view_salary' => ['nullable', 'boolean'],
            'can_edit_salary' => ['nullable', 'boolean'],
            'can_export_salary' => ['nullable', 'boolean'],
            'requires_approval' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $policy = CompensationAccessPolicy::create([
            ...$validated,
            'can_view_salary' => (bool) ($validated['can_view_salary'] ?? false),
            'can_edit_salary' => (bool) ($validated['can_edit_salary'] ?? false),
            'can_export_salary' => (bool) ($validated['can_export_salary'] ?? false),
            'requires_approval' => (bool) ($validated['requires_approval'] ?? true),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('compensation_access_policy.created', "Policy akses {$policy->policy_name} dibuat.", $policy, [], $policy->toArray(), $request);

        return redirect()->route('payroll.salary-structures')->with('success', 'Policy akses kompensasi berhasil disimpan.');
    }

    private function authorizePayroll(): void
    {
        abort_unless(auth()->user()?->can('manage payroll settings') || auth()->user()?->can('manage modules'), 403);
    }
}
