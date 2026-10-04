<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\PayrollBpjsProfile;
use App\Models\TaxProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollBpjsProfileController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('bpjs-profiles', [
            'active' => 'bpjs-profiles',
            'employees' => EmployeeProfile::with(['company', 'branch', 'taxProfile', 'bpjsProfile'])->orderBy('full_name')->get(),
            'taxProfiles' => TaxProfile::orderBy('employee_name')->limit(300)->get(),
            'profiles' => PayrollBpjsProfile::with(['employeeProfile.company', 'employeeProfile.branch', 'taxProfile'])
                ->latest('effective_date')
                ->latest()
                ->limit(150)
                ->get(),
            'summary' => [
                'active' => PayrollBpjsProfile::where('is_active', true)->count(),
                'verified' => PayrollBpjsProfile::where('verification_status', 'verified')->count(),
                'draft' => PayrollBpjsProfile::where('verification_status', 'draft')->count(),
                'inactive' => PayrollBpjsProfile::where('is_active', false)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'tax_profile_id' => ['nullable', 'exists:tax_profiles,id'],
            'bpjs_kesehatan_number' => ['nullable', 'string', 'max:80'],
            'bpjs_ketenagakerjaan_number' => ['nullable', 'string', 'max:80'],
            'bpjs_class' => ['required', Rule::in(['Kelas 1', 'Kelas 2', 'Kelas 3'])],
            'effective_date' => ['required', 'date'],
            'health_contribution_base' => ['required', 'numeric', 'min:0'],
            'employment_contribution_base' => ['required', 'numeric', 'min:0'],
            'health_employee_rate' => ['required', 'numeric', 'min:0'],
            'health_employer_rate' => ['required', 'numeric', 'min:0'],
            'jht_employee_rate' => ['required', 'numeric', 'min:0'],
            'jht_employer_rate' => ['required', 'numeric', 'min:0'],
            'jp_employee_rate' => ['required', 'numeric', 'min:0'],
            'jp_employer_rate' => ['required', 'numeric', 'min:0'],
            'verification_status' => ['required', Rule::in(['draft', 'verified', 'needs_review'])],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $employee = EmployeeProfile::with(['company', 'branch'])->findOrFail($data['employee_profile_id']);
        $healthBase = (float) $data['health_contribution_base'];
        $employmentBase = (float) $data['employment_contribution_base'];

        $profile = PayrollBpjsProfile::create([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
            'calculation_snapshot' => [
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->full_name,
                'company' => $employee->company?->code,
                'branch' => $employee->branch?->code,
                'health_employee_amount' => $healthBase * ((float) $data['health_employee_rate'] / 100),
                'health_employer_amount' => $healthBase * ((float) $data['health_employer_rate'] / 100),
                'jht_employee_amount' => $employmentBase * ((float) $data['jht_employee_rate'] / 100),
                'jht_employer_amount' => $employmentBase * ((float) $data['jht_employer_rate'] / 100),
                'jp_employee_amount' => $employmentBase * ((float) $data['jp_employee_rate'] / 100),
                'jp_employer_amount' => $employmentBase * ((float) $data['jp_employer_rate'] / 100),
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('payroll_bpjs_profile.created', "Profil BPJS payroll dibuat untuk {$employee->employee_number}.", $profile, [], $profile->toArray(), $request);

        return redirect()->route('payroll.bpjs-profiles')->with('success', 'Profil BPJS payroll berhasil disimpan.');
    }
}
