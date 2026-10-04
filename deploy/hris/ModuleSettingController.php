<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyModuleSetting;
use App\Models\ModuleSetting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ModuleSettingController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage modules');

        $companies = Company::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
        $selectedCompany = $companies->firstWhere('id', (int) request('company_id')) ?: $companies->first();
        $overrides = $selectedCompany
            ? CompanyModuleSetting::query()
                ->where('company_id', $selectedCompany->id)
                ->get()
                ->keyBy('module_setting_id')
            : collect();

        $modules = ModuleSetting::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(function (ModuleSetting $module) use ($overrides) {
                $override = $overrides->get($module->id);
                $module->effective_is_enabled = $override ? $override->is_enabled : $module->is_enabled;
                $module->has_company_override = (bool) $override;
            })
            ->groupBy('group');

        return view('settings.modules', [
            'active' => 'modules',
            'companies' => $companies,
            'selectedCompany' => $selectedCompany,
            'modules' => $modules,
        ]);
    }

    public function toggle(Request $request, ModuleSetting $moduleSetting): RedirectResponse
    {
        Gate::authorize('manage modules');

        $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'is_enabled' => ['required', 'boolean'],
        ]);

        if ($moduleSetting->is_core && ! $request->boolean('is_enabled')) {
            return redirect()
                ->route('settings.modules', ['company_id' => $request->integer('company_id')])
                ->with('error', "Modul core '{$moduleSetting->name}' tidak bisa dinonaktifkan.");
        }

        $company = Company::findOrFail($request->integer('company_id'));
        $status = $request->boolean('is_enabled') ? 'diaktifkan' : 'dinonaktifkan';

        $oldOverride = CompanyModuleSetting::query()
            ->where('company_id', $company->id)
            ->where('module_setting_id', $moduleSetting->id)
            ->first();

        $override = CompanyModuleSetting::updateOrCreate(
            [
                'company_id' => $company->id,
                'module_setting_id' => $moduleSetting->id,
            ],
            ['is_enabled' => $request->boolean('is_enabled')]
        );

        AuditLogger::record(
            'company_module.toggled',
            "Modul '{$moduleSetting->name}' {$status} untuk {$company->code}.",
            $override,
            ['is_enabled' => $oldOverride?->is_enabled],
            ['is_enabled' => $override->is_enabled, 'company_id' => $company->id, 'module_setting_id' => $moduleSetting->id],
            $request
        );

        return redirect()
            ->route('settings.modules', ['company_id' => $company->id])
            ->with('success', "Modul '{$moduleSetting->name}' berhasil {$status} untuk {$company->code}.");
    }
}
