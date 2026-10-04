<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PsychotestPackage;
use App\Models\PsychotestProvider;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PsychotestIntegrationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage recruitment');

        $providers = PsychotestProvider::with(['company', 'packages'])
            ->withCount('packages')
            ->latest()
            ->limit(120)
            ->get();

        $packages = PsychotestPackage::with('provider')
            ->latest()
            ->limit(160)
            ->get();

        return view('recruitment-psychotest-integrations', [
            'active' => 'recruitment-psychotest-integrations',
            'companies' => Company::orderBy('code')->get(),
            'providers' => $providers,
            'packages' => $packages,
            'summary' => [
                'providers' => $providers->count(),
                'connected' => $providers->where('connection_status', 'connected')->count(),
                'packages' => $packages->count(),
                'active_packages' => $packages->where('status', 'active')->count(),
                'auto_pull' => $packages->where('auto_pull_result', true)->count(),
            ],
        ]);
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'provider_code' => ['required', 'string', 'max:100', 'unique:psychotest_providers,provider_code'],
            'provider_name' => ['required', 'string', 'max:255'],
            'provider_type' => ['required', Rule::in(['internal_engine', 'external_api', 'portal_link', 'manual_upload', 'sftp_batch'])],
            'base_url' => ['nullable', 'string', 'max:255'],
            'auth_method' => ['required', Rule::in(['internal', 'api_key', 'oauth2', 'basic_auth', 'sftp_key', 'manual'])],
            'credential_reference' => ['nullable', 'string', 'max:255'],
            'webhook_url' => ['nullable', 'string', 'max:255'],
            'callback_secret_reference' => ['nullable', 'string', 'max:255'],
            'sla_result_hours' => ['nullable', 'string', 'max:20'],
            'owner_name' => ['nullable', 'string', 'max:160'],
            'connection_status' => ['required', Rule::in(['draft', 'credential_ready', 'connected', 'testing', 'error', 'disabled'])],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $provider = PsychotestProvider::create([
            ...collect($data)->except('is_active')->all(),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('psychotest_provider.created', "Provider psikotest {$provider->provider_code} dibuat.", $provider, [], $provider->toArray(), $request);

        return redirect()->route('recruitment.psychotest-integrations')->with('success', 'Provider psikotest berhasil disimpan.');
    }

    public function storePackage(Request $request): RedirectResponse
    {
        Gate::authorize('manage recruitment');

        $data = $request->validate([
            'psychotest_provider_id' => ['required', 'exists:psychotest_providers,id'],
            'package_code' => ['required', 'string', 'max:100', 'unique:psychotest_packages,package_code'],
            'package_name' => ['required', 'string', 'max:255'],
            'assessment_category' => ['required', Rule::in(['personality', 'cognitive', 'integrity', 'leadership', 'technical', 'culture_fit', 'complete_battery'])],
            'target_role_level' => ['required', Rule::in(['all_level', 'staff', 'supervisor', 'manager', 'executive', 'operator'])],
            'estimated_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'competency_mapping' => ['nullable', 'string', 'max:1000'],
            'result_scale' => ['required', Rule::in(['score_0_100', 'sten', 'percentile', 'fit_recommendation', 'provider_report'])],
            'interpretation_method' => ['required', Rule::in(['provider_report', 'hr_mapping', 'ai_summary_reviewed', 'manual_review'])],
            'auto_pull_result' => ['nullable', 'boolean'],
            'requires_candidate_consent' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'active', 'testing', 'retired'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $package = PsychotestPackage::create([
            ...collect($data)->except(['competency_mapping', 'auto_pull_result', 'requires_candidate_consent'])->all(),
            'competency_mapping' => collect(explode(',', (string) ($data['competency_mapping'] ?? '')))->map(fn ($item) => trim($item))->filter()->values()->all(),
            'auto_pull_result' => (bool) ($data['auto_pull_result'] ?? false),
            'requires_candidate_consent' => (bool) ($data['requires_candidate_consent'] ?? false),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('psychotest_package.created', "Paket psikotest {$package->package_code} dibuat.", $package, [], $package->toArray(), $request);

        return redirect()->route('recruitment.psychotest-integrations')->with('success', 'Paket psikotest berhasil disimpan.');
    }
}
