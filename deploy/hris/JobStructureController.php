<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\JobGrade;
use App\Models\JobPosition;
use App\Models\OrganizationUnit;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JobStructureController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage settings');

        return view('job-structure', [
            'active' => 'job-structure',
            'companies' => Company::orderBy('name')->get(['id', 'code', 'name']),
            'branches' => Branch::with('company')->orderBy('code')->get(['id', 'company_id', 'code', 'name']),
            'organizationUnits' => OrganizationUnit::with(['company', 'branch', 'parent'])->orderBy('code')->get(),
            'jobGrades' => JobGrade::with('company')->orderBy('level')->orderBy('code')->get(),
            'costCenters' => CostCenter::with(['company', 'branch'])->orderBy('code')->get(),
            'jobPositions' => JobPosition::with(['company', 'organizationUnit', 'jobGrade', 'costCenter'])->orderBy('code')->get(),
            'summary' => [
                'units' => OrganizationUnit::count(),
                'positions' => JobPosition::count(),
                'grades' => JobGrade::count(),
                'cost_centers' => CostCenter::count(),
            ],
        ]);
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

        $request->validate(['code' => ['unique:organization_units,code,NULL,id,company_id,' . $data['company_id']]]);

        $unit = OrganizationUnit::create($data + ['is_active' => true]);

        AuditLogger::record('organization_unit.created', "Unit organisasi '{$unit->code}' dibuat.", $unit, [], $unit->toArray(), $request);

        return redirect()->route('settings.job-structure')->with('success', 'Unit organisasi berhasil ditambahkan.');
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

        $request->validate(['code' => ['unique:job_grades,code,NULL,id,company_id,' . $data['company_id']]]);

        $grade = JobGrade::create($data + ['is_active' => true]);

        AuditLogger::record('job_grade.created', "Grade jabatan '{$grade->code}' dibuat.", $grade, [], $grade->toArray(), $request);

        return redirect()->route('settings.job-structure')->with('success', 'Grade jabatan berhasil ditambahkan.');
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

        $request->validate(['code' => ['unique:cost_centers,code,NULL,id,company_id,' . $data['company_id']]]);

        $data['is_billable'] = $request->boolean('is_billable');
        $costCenter = CostCenter::create($data + ['is_active' => true]);

        AuditLogger::record('cost_center.created', "Cost center '{$costCenter->code}' dibuat.", $costCenter, [], $costCenter->toArray(), $request);

        return redirect()->route('settings.job-structure')->with('success', 'Cost center berhasil ditambahkan.');
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

        $request->validate(['code' => ['unique:job_positions,code,NULL,id,company_id,' . $data['company_id']]]);

        $position = JobPosition::create($data + ['is_active' => true]);

        AuditLogger::record('job_position.created', "Posisi '{$position->code}' dibuat.", $position, [], $position->toArray(), $request);

        return redirect()->route('settings.job-structure')->with('success', 'Posisi jabatan berhasil ditambahkan.');
    }
}
