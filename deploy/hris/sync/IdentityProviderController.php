<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\IdentityProviderSetting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IdentityProviderController extends Controller
{
    public function index(): View
    {
        $this->authorizeAccess();

        $providers = IdentityProviderSetting::with('company')->latest()->limit(120)->get();

        return view('integrations.identity-providers', [
            'active' => 'identity-providers',
            'companies' => Company::orderBy('code')->get(),
            'providers' => $providers,
            'summary' => [
                'providers' => $providers->count(),
                'active' => $providers->where('status', 'active')->count(),
                'enforced' => $providers->where('enforce_sso', true)->count(),
                'auto_provision' => $providers->where('auto_provision', true)->count(),
            ],
            'readiness' => $this->readiness($providers),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'provider_code' => ['required', 'string', 'max:100', 'unique:identity_provider_settings,provider_code'],
            'provider_name' => ['required', 'string', 'max:255'],
            'protocol' => ['required', Rule::in(['saml2', 'oidc'])],
            'domain_hint' => ['nullable', 'string', 'max:120'],
            'issuer_url' => ['nullable', 'url', 'max:255'],
            'sso_login_url' => ['nullable', 'url', 'max:255'],
            'sso_logout_url' => ['nullable', 'url', 'max:255'],
            'metadata_url' => ['nullable', 'url', 'max:255'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'certificate_fingerprint' => ['nullable', 'string', 'max:255'],
            'claim_email' => ['nullable', 'string', 'max:80'],
            'claim_name' => ['nullable', 'string', 'max:80'],
            'claim_employee_number' => ['nullable', 'string', 'max:80'],
            'default_role' => ['nullable', 'string', 'max:80'],
            'auto_provision' => ['nullable', 'boolean'],
            'jit_update_profile' => ['nullable', 'boolean'],
            'enforce_sso' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'testing', 'active', 'disabled'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $provider = IdentityProviderSetting::create([
            'company_id' => $data['company_id'] ?? null,
            'provider_code' => strtoupper($data['provider_code']),
            'provider_name' => $data['provider_name'],
            'protocol' => $data['protocol'],
            'domain_hint' => $data['domain_hint'] ?? null,
            'issuer_url' => $data['issuer_url'] ?? null,
            'sso_login_url' => $data['sso_login_url'] ?? null,
            'sso_logout_url' => $data['sso_logout_url'] ?? null,
            'metadata_url' => $data['metadata_url'] ?? null,
            'client_id' => $data['client_id'] ?? null,
            'certificate_fingerprint' => $data['certificate_fingerprint'] ?? null,
            'claim_mapping' => [
                'email' => $data['claim_email'] ?: 'email',
                'name' => $data['claim_name'] ?: 'name',
                'employee_number' => $data['claim_employee_number'] ?: 'employee_number',
            ],
            'role_mapping' => [
                'default_role' => $data['default_role'] ?: 'Employee',
                'source' => 'idp_group_or_default_role',
            ],
            'auto_provision' => (bool) ($data['auto_provision'] ?? false),
            'jit_update_profile' => (bool) ($data['jit_update_profile'] ?? true),
            'enforce_sso' => (bool) ($data['enforce_sso'] ?? false),
            'status' => $data['status'],
            'last_tested_at' => $data['status'] === 'testing' ? now() : null,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('identity_provider.created', "Identity provider {$provider->provider_code} dibuat.", $provider, [], $provider->toArray(), $request);

        return redirect()->route('integrations.identity-providers')->with('success', 'Identity provider berhasil disimpan.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            Gate::allows('manage modules') || Gate::allows('view delivery progress'),
            403
        );
    }

    private function readiness($providers): array
    {
        $active = $providers->where('status', 'active')->count();
        $withLogin = $providers->filter(fn ($provider) => filled($provider->sso_login_url) || filled($provider->metadata_url))->count();
        $withMapping = $providers->filter(fn ($provider) => filled($provider->claim_mapping['email'] ?? null) && filled($provider->claim_mapping['name'] ?? null))->count();
        $withSecurity = $providers->filter(fn ($provider) => filled($provider->certificate_fingerprint) || filled($provider->client_id))->count();
        $total = max(1, $providers->count());

        return [
            'score' => min(100, (int) round((($active + $withLogin + $withMapping + $withSecurity) / ($total * 4)) * 100)),
            'active' => $active,
            'with_login' => $withLogin,
            'with_mapping' => $withMapping,
            'with_security' => $withSecurity,
            'actions' => [
                'Upload metadata IdP atau isi SSO Login URL sebelum enforce SSO.',
                'Validasi claim email, nama, dan nomor karyawan agar auto-provision tidak salah mapping.',
                'Aktifkan enforce SSO bertahap per perusahaan setelah pilot user berhasil login.',
            ],
        ];
    }
}
