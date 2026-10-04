@extends('layouts.sidebar')

@section('title', 'SSO & Identity Provider')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">SSO & Identity Provider</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola koneksi SAML/OIDC, domain perusahaan, claim mapping, dan kebijakan auto-provision user.</p>
        </div>
        <a href="{{ route('settings.users') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-users-gear mr-2"></i> User Access
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Provider</div><div class="mt-1 text-xl font-semibold text-gray-900">{{ $summary['providers'] }}</div><div class="text-xs text-gray-400">Terdaftar</div></div>
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Aktif</div><div class="mt-1 text-xl font-semibold text-green-600">{{ $summary['active'] }}</div><div class="text-xs text-gray-400">Siap login</div></div>
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Enforced</div><div class="mt-1 text-xl font-semibold text-blue-600">{{ $summary['enforced'] }}</div><div class="text-xs text-gray-400">Wajib SSO</div></div>
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Auto Provision</div><div class="mt-1 text-xl font-semibold text-amber-600">{{ $summary['auto_provision'] }}</div><div class="text-xs text-gray-400">JIT user</div></div>
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Readiness</div><div class="mt-1 text-xl font-semibold text-blue-600">{{ $readiness['score'] }}%</div><div class="text-xs text-gray-400">Konfigurasi</div></div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Tambah Identity Provider</h2>
                <p class="text-sm text-gray-500">Isi metadata IdP perusahaan. Enforce SSO sebaiknya dinyalakan setelah pilot user berhasil.</p>
            </div>
            <div class="group relative">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-80 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    SAML biasanya memakai metadata URL, login URL, dan certificate fingerprint. OIDC biasanya memakai issuer URL dan client ID. Claim mapping menentukan email, nama, dan nomor karyawan yang dipakai untuk link user HRIS.
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('integrations.identity-providers.store') }}" class="mt-5 space-y-4">
            @csrf
            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Identitas Provider</h3>
                <div class="mt-3 grid gap-3 md:grid-cols-4">
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</span>
                        <select name="company_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Semua perusahaan</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Provider</span>
                        <input name="provider_code" required placeholder="Contoh: BMP-AZUREAD" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Provider</span>
                        <input name="provider_name" required placeholder="Contoh: Azure AD BMP" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Protocol</span>
                        <select name="protocol" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="saml2">SAML 2.0</option>
                            <option value="oidc">OIDC</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Domain Hint</span>
                        <input name="domain_hint" placeholder="contoh: kurmigo.id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Issuer URL</span>
                        <input name="issuer_url" placeholder="https://login.provider/tenant" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">SSO Login URL</span>
                        <input name="sso_login_url" placeholder="https://idp/sso/login" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Metadata URL</span>
                        <input name="metadata_url" placeholder="https://idp/metadata.xml" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Claim Mapping & Security</h3>
                <div class="mt-3 grid gap-3 md:grid-cols-4">
                    <input name="claim_email" placeholder="Claim email, default: email" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="claim_name" placeholder="Claim nama, default: name" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="claim_employee_number" placeholder="Claim NIK, default: employee_number" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="default_role" placeholder="Default role, contoh: Employee" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="client_id" placeholder="Client ID untuk OIDC" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="certificate_fingerprint" placeholder="Certificate fingerprint untuk SAML" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="draft">Draft</option>
                        <option value="testing">Testing</option>
                        <option value="active">Active</option>
                        <option value="disabled">Disabled</option>
                    </select>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-600"><input type="checkbox" name="auto_provision" value="1" class="mr-1"> Auto user</label>
                        <label class="rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-600"><input type="checkbox" name="jit_update_profile" value="1" checked class="mr-1"> Update profil</label>
                        <label class="rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-600"><input type="checkbox" name="enforce_sso" value="1" class="mr-1"> Wajib SSO</label>
                    </div>
                    <label class="block md:col-span-4">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</span>
                        <textarea name="notes" rows="3" placeholder="Catatan IdP, pilot user, atau constraint security." class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    <i class="fas fa-shield-halved"></i> Simpan Provider
                </button>
            </div>
        </form>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Identity Provider</h2>
            <p class="text-sm text-gray-500">Pantau status SSO, scope perusahaan, dan kesiapan mapping sebelum enforce login.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Provider</th>
                        <th class="px-5 py-3">Scope</th>
                        <th class="px-5 py-3">Endpoint</th>
                        <th class="px-5 py-3">Mapping</th>
                        <th class="px-5 py-3">Policy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($providers as $provider)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $provider->provider_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $provider->provider_code }} - {{ strtoupper($provider->protocol) }}</div>
                                <span class="mt-2 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ strtoupper($provider->status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $provider->company?->code ?? 'ALL' }}</div>
                                <div class="text-xs text-gray-500">{{ $provider->domain_hint ?: 'Domain belum diisi' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="max-w-xs truncate text-xs text-gray-600">{{ $provider->issuer_url ?: $provider->metadata_url ?: 'Metadata belum diisi' }}</div>
                                <div class="max-w-xs truncate text-xs text-gray-500">{{ $provider->sso_login_url ?: 'Login URL belum diisi' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-600">Email: {{ $provider->claim_mapping['email'] ?? '-' }}</div>
                                <div class="text-xs text-gray-600">Nama: {{ $provider->claim_mapping['name'] ?? '-' }}</div>
                                <div class="text-xs text-gray-600">NIK: {{ $provider->claim_mapping['employee_number'] ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-600">{{ $provider->auto_provision ? 'Auto-provision aktif' : 'Auto-provision off' }}</div>
                                <div class="text-xs text-gray-600">{{ $provider->jit_update_profile ? 'JIT profile update' : 'Tidak update profil' }}</div>
                                <div class="text-xs text-gray-600">{{ $provider->enforce_sso ? 'SSO wajib' : 'Password login masih boleh' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada identity provider.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
