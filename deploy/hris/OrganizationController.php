<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\JobGrade;
use App\Models\JobPosition;
use App\Models\LegalEntity;
use App\Models\OrganizationUnit;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage settings');

        $companies = Company::withCount(['legalEntities', 'branches', 'users'])
            ->with([
                'legalEntities',
                'branches' => fn ($query) => $query->withCount('users')->orderByDesc('is_head_office')->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();

        $organizationUnits = OrganizationUnit::with(['company', 'branch', 'parent'])->orderBy('code')->get();
        $jobGrades = JobGrade::with('company')->orderBy('level')->orderBy('code')->get();
        $costCenters = CostCenter::with(['company', 'branch'])->orderBy('code')->get();
        $jobPositions = JobPosition::with(['company', 'organizationUnit', 'jobGrade', 'costCenter'])->orderBy('code')->get();

        return view('settings.organization', [
            'active' => 'organization',
            'companies' => $companies,
            'companyOptions' => Company::orderBy('name')->get(['id', 'code', 'name']),
            'branchOptions' => Branch::with('company')->orderBy('code')->get(['id', 'company_id', 'code', 'name']),
            'legalEntityOptions' => LegalEntity::with('company')->orderBy('name')->get(['id', 'company_id', 'code', 'name']),
            'organizationUnitOptions' => $organizationUnits,
            'jobGradeOptions' => $jobGrades,
            'costCenterOptions' => $costCenters,
            'organizationUnitsByCompany' => $organizationUnits->groupBy('company_id'),
            'jobGradesByCompany' => $jobGrades->groupBy('company_id'),
            'costCentersByCompany' => $costCenters->groupBy('company_id'),
            'jobPositionsByCompany' => $jobPositions->groupBy('company_id'),
            'summary' => [
                'companies' => Company::count(),
                'legal_entities' => LegalEntity::count(),
                'branches' => Branch::count(),
                'organization_units' => OrganizationUnit::count(),
                'job_positions' => JobPosition::count(),
                'job_grades' => JobGrade::count(),
                'cost_centers' => CostCenter::count(),
            ],
        ]);
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:companies,code'],
            'name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $company = Company::create($data + ['is_active' => true]);

        AuditLogger::record(
            'company.created',
            "Perusahaan '{$company->code}' dibuat.",
            $company,
            [],
            $company->only(['code', 'name', 'brand_name', 'tax_id', 'address', 'is_active']),
            $request
        );

        return redirect()->route('settings.organization')->with('success', 'Perusahaan berhasil ditambahkan.');
    }

    public function storeLegalEntity(Request $request): RedirectResponse
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code' => ['required', 'string', 'max:30', 'unique:legal_entities,code'],
            'name' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'nib' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $data['is_primary'] = $request->boolean('is_primary');
        $data['is_active'] = true;

        $legalEntity = LegalEntity::create($data);

        AuditLogger::record(
            'legal_entity.created',
            "Badan hukum '{$legalEntity->code}' dibuat.",
            $legalEntity,
            [],
            $legalEntity->only(['company_id', 'code', 'name', 'npwp', 'nib', 'is_primary', 'is_active']),
            $request
        );

        return redirect()->route('settings.organization')->with('success', 'Badan hukum berhasil ditambahkan.');
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'legal_entity_id' => ['nullable', 'exists:legal_entities,id'],
            'code' => ['required', 'string', 'max:30', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_head_office' => ['nullable', 'boolean'],
        ]);

        $data['is_head_office'] = $request->boolean('is_head_office');
        $data['is_active'] = true;

        $branch = Branch::create($data);

        AuditLogger::record(
            'branch.created',
            "Cabang '{$branch->code}' dibuat.",
            $branch,
            [],
            $branch->only(['company_id', 'legal_entity_id', 'code', 'name', 'city', 'is_head_office', 'is_active']),
            $request
        );

        return redirect()->route('settings.organization')->with('success', 'Cabang atau site berhasil ditambahkan.');
    }

    public function storeOrganizationUnit(Request $request): RedirectResponse
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'parent_id' => ['nullable', 'exists:organization_units,id'],
            'code' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:160'],
            'unit_type' => ['required', 'in:division,department,section,team'],
            'manager_name' => ['nullable', 'string', 'max:160'],
        ]);

        $request->validate([
            'code' => ['unique:organization_units,code,NULL,id,company_id,' . $data['company_id']],
        ]);

        $unit = OrganizationUnit::create($data + ['is_active' => true]);

        AuditLogger::record(
            'organization_unit.created',
            "Unit organisasi '{$unit->code}' dibuat.",
            $unit,
            [],
            $unit->only(['company_id', 'branch_id', 'parent_id', 'code', 'name', 'unit_type', 'manager_name', 'is_active']),
            $request
        );

        return redirect()->route('settings.organization')->with('success', 'Unit organisasi berhasil ditambahkan.');
    }

    public function storeJobGrade(Request $request): RedirectResponse
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'code' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:160'],
            'level' => ['required', 'integer', 'min:1', 'max:99'],
            'minimum_salary' => ['nullable', 'numeric', 'min:0'],
            'maximum_salary' => ['nullable', 'numeric', 'min:0'],
        ]);

        $request->validate([
            'code' => ['unique:job_grades,code,NULL,id,company_id,' . $data['company_id']],
        ]);

        $grade = JobGrade::create($data + ['is_active' => true]);

        AuditLogger::record(
            'job_grade.created',
            "Grade jabatan '{$grade->code}' dibuat.",
            $grade,
            [],
            $grade->only(['company_id', 'code', 'name', 'level', 'minimum_salary', 'maximum_salary', 'is_active']),
            $request
        );

        return redirect()->route('settings.organization')->with('success', 'Grade jabatan berhasil ditambahkan.');
    }

    public function storeCostCenter(Request $request): RedirectResponse
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:160'],
            'owner_name' => ['nullable', 'string', 'max:160'],
            'is_billable' => ['nullable', 'boolean'],
        ]);

        $request->validate([
            'code' => ['unique:cost_centers,code,NULL,id,company_id,' . $data['company_id']],
        ]);

        $data['is_billable'] = $request->boolean('is_billable');
        $costCenter = CostCenter::create($data + ['is_active' => true]);

        AuditLogger::record(
            'cost_center.created',
            "Cost center '{$costCenter->code}' dibuat.",
            $costCenter,
            [],
            $costCenter->only(['company_id', 'branch_id', 'code', 'name', 'owner_name', 'is_billable', 'is_active']),
            $request
        );

        return redirect()->route('settings.organization')->with('success', 'Cost center berhasil ditambahkan.');
    }

    public function storeJobPosition(Request $request): RedirectResponse
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'organization_unit_id' => ['nullable', 'exists:organization_units,id'],
            'job_grade_id' => ['nullable', 'exists:job_grades,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'code' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:160'],
            'employment_category' => ['required', 'in:staff,supervisor,manager,executive,temporary'],
            'headcount_plan' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $request->validate([
            'code' => ['unique:job_positions,code,NULL,id,company_id,' . $data['company_id']],
        ]);

        $position = JobPosition::create($data + ['is_active' => true]);

        AuditLogger::record(
            'job_position.created',
            "Posisi '{$position->code}' dibuat.",
            $position,
            [],
            $position->only(['company_id', 'organization_unit_id', 'job_grade_id', 'cost_center_id', 'code', 'name', 'employment_category', 'headcount_plan', 'is_active']),
            $request
        );

        return redirect()->route('settings.organization')->with('success', 'Posisi jabatan berhasil ditambahkan.');
    }
}
