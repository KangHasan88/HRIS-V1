<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\EmployeeProfile;
use App\Models\JobGrade;
use App\Models\JobPosition;
use App\Models\OrganizationUnit;
use App\Models\TaxProfile;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EmployeeProfileController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        return view('employee-profiles', [
            'active' => 'employees',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'taxProfiles' => TaxProfile::where('is_active', true)->orderBy('employee_name')->get(),
            'organizationUnits' => OrganizationUnit::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'jobPositions' => JobPosition::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'jobGrades' => JobGrade::where('is_active', true)->orderBy('level')->get(['id', 'code', 'name', 'level']),
            'costCenters' => CostCenter::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'employees' => EmployeeProfile::with(['company', 'branch', 'taxProfile', 'familyMembers', 'bankAccounts', 'bpjsProfile'])->latest()->limit(100)->get(),
            'summary' => [
                'total' => EmployeeProfile::count(),
                'active' => EmployeeProfile::where('is_active', true)->count(),
                'permanent' => EmployeeProfile::where('employment_status', 'permanent')->count(),
                'contract' => EmployeeProfile::where('employment_status', 'contract')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'tax_profile_id' => ['nullable', 'exists:tax_profiles,id'],
            'employee_number' => ['required', 'string', 'max:40', 'unique:employee_profiles,employee_number'],
            'full_name' => ['required', 'string', 'max:160'],
            'employment_status' => ['required', 'in:probation,contract,permanent,intern,alumni'],
            'position' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'grade' => ['nullable', 'string', 'max:60'],
            'cost_center' => ['nullable', 'string', 'max:80'],
            'join_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date'],
            'work_email' => ['nullable', 'email', 'max:160'],
            'personal_email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:500'],
            'family_members' => ['nullable', 'array'],
            'family_members.*.name' => ['nullable', 'string', 'max:120'],
            'family_members.*.relationship' => ['nullable', 'string', 'max:60'],
            'family_members.*.birth_date' => ['nullable', 'date'],
            'family_members.*.is_dependent' => ['nullable', 'boolean'],
            'family_name' => ['nullable', 'string', 'max:120'],
            'family_relationship' => ['nullable', 'string', 'max:60'],
            'family_birth_date' => ['nullable', 'date'],
            'family_is_dependent' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:80'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'account_holder_name' => ['nullable', 'string', 'max:160'],
            'bpjs_kesehatan_number' => ['nullable', 'string', 'max:80'],
            'bpjs_ketenagakerjaan_number' => ['nullable', 'string', 'max:80'],
            'bpjs_class' => ['nullable', 'string', 'max:40'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $employee = EmployeeProfile::create([
            ...collect($data)->only([
                'user_id', 'company_id', 'branch_id', 'tax_profile_id', 'employee_number', 'full_name',
                'employment_status', 'position', 'department', 'grade', 'cost_center', 'join_date',
                'contract_end_date', 'work_email', 'personal_email', 'phone', 'emergency_contact_name',
                'emergency_contact_phone',
            ])->all(),
            'address_snapshot' => ['address' => $data['address'] ?? null],
            'document_summary' => [
                'tax_profile_linked' => filled($data['tax_profile_id'] ?? null),
                'bank_account_linked' => filled($data['account_number'] ?? null),
                'bpjs_linked' => filled($data['bpjs_kesehatan_number'] ?? null) || filled($data['bpjs_ketenagakerjaan_number'] ?? null),
            ],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => auth()->id(),
        ]);

        $familyMembers = collect($data['family_members'] ?? [])
            ->filter(fn (array $family) => filled($family['name'] ?? null))
            ->values();

        if ($familyMembers->isEmpty() && filled($data['family_name'] ?? null)) {
            $familyMembers = collect([[
                'name' => $data['family_name'],
                'relationship' => $data['family_relationship'] ?? 'Keluarga',
                'birth_date' => $data['family_birth_date'] ?? null,
                'is_dependent' => $data['family_is_dependent'] ?? false,
            ]]);
        }

        foreach ($familyMembers as $family) {
            $employee->familyMembers()->create([
                'name' => $family['name'],
                'relationship' => $family['relationship'] ?: 'Keluarga',
                'birth_date' => $family['birth_date'] ?? null,
                'is_dependent' => (bool) ($family['is_dependent'] ?? false),
            ]);
        }

        if (filled($data['bank_name'] ?? null) && filled($data['account_number'] ?? null)) {
            $employee->bankAccounts()->create([
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'account_holder_name' => $data['account_holder_name'] ?: $employee->full_name,
                'is_primary' => true,
            ]);
        }

        if (filled($data['bpjs_kesehatan_number'] ?? null) || filled($data['bpjs_ketenagakerjaan_number'] ?? null)) {
            $employee->bpjsProfile()->create([
                'bpjs_kesehatan_number' => $data['bpjs_kesehatan_number'] ?? null,
                'bpjs_ketenagakerjaan_number' => $data['bpjs_ketenagakerjaan_number'] ?? null,
                'bpjs_class' => $data['bpjs_class'] ?? null,
                'registered_at' => $data['join_date'] ?? null,
                'coverage_snapshot' => [
                    'health' => filled($data['bpjs_kesehatan_number'] ?? null),
                    'employment' => filled($data['bpjs_ketenagakerjaan_number'] ?? null),
                ],
            ]);
        }

        AuditLogger::record(
            'employee_profile.created',
            "Profil karyawan 360 {$employee->employee_number} dibuat.",
            $employee,
            [],
            $employee->load(['familyMembers', 'bankAccounts', 'bpjsProfile'])->only(['employee_number', 'full_name', 'employment_status', 'position']),
            $request
        );

        return redirect()->route('hris.employees')->with('success', 'Profil karyawan 360 berhasil dibuat.');
    }
}
