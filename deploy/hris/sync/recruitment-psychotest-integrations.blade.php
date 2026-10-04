@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Integrasi Psikotest</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola provider eksternal, paket assessment, credential reference, webhook, dan kesiapan sinkron hasil.</p>
        </div>
        <a href="{{ route('recruitment.candidate-checks') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-user-check"></i>
            Pengecekan Kandidat
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Provider', 'value' => $summary['providers'], 'color' => 'text-gray-900', 'hint' => 'Partner terdaftar'],
                ['label' => 'Connected', 'value' => $summary['connected'], 'color' => 'text-green-600', 'hint' => 'Siap integrasi'],
                ['label' => 'Paket Test', 'value' => $summary['packages'], 'color' => 'text-blue-600', 'hint' => 'Battery/assessment'],
                ['label' => 'Paket Aktif', 'value' => $summary['active_packages'], 'color' => 'text-green-600', 'hint' => 'Bisa dipakai invite'],
                ['label' => 'Auto Pull', 'value' => $summary['auto_pull'], 'color' => 'text-amber-600', 'hint' => 'Hasil ditarik otomatis'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.95fr_1.05fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Provider</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Integrasi Provider Psikotest',
                        'description' => 'Form ini mencatat koneksi ke vendor psikotest eksternal. Credential asli cukup disimpan di server secret manager, field di sini hanya reference.',
                        'steps' => [
                            'Isi kode provider, nama vendor, dan metode koneksi.',
                            'Isi base URL, webhook, serta credential reference jika provider sudah siap integrasi.',
                            'Set status Connected hanya jika credential dan callback sudah dites.',
                            'Provider aktif akan tersedia saat HR membuat undangan psikotest kandidat.',
                        ],
                        'notes' => [
                            'Jangan menulis API key asli di field credential reference.',
                            'Portal Link atau Manual Upload tetap didukung jika vendor belum punya API.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Foundation untuk koneksi API, portal link, SFTP batch, atau upload hasil manual.</p>
            </div>
            <form action="{{ route('recruitment.psychotest-integrations.providers') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Provider</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Provider</label>
                            <input name="provider_code" required placeholder="Contoh: PSY-PROV-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Provider</label>
                            <input name="provider_name" required placeholder="Contoh: Assessment Partner Indonesia" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Provider</label>
                            <select name="provider_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="internal_engine">Internal Engine</option>
                                <option value="external_api">External API</option>
                                <option value="portal_link">Portal Link</option>
                                <option value="sftp_batch">SFTP Batch</option>
                                <option value="manual_upload">Manual Upload</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Koneksi & Security</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Base URL</label>
                            <input name="base_url" placeholder="https://api.provider.com" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Auth Method</label>
                            <select name="auth_method" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="internal">Internal</option>
                                <option value="api_key">API Key</option>
                                <option value="oauth2">OAuth2</option>
                                <option value="basic_auth">Basic Auth</option>
                                <option value="sftp_key">SFTP Key</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Credential Reference</label>
                            <input name="credential_reference" placeholder="Contoh: secret://psychotest/main" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Webhook URL</label>
                            <input name="webhook_url" placeholder="Callback URL dari HRIS untuk hasil test" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Operasional</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Callback Secret Reference</label>
                            <input name="callback_secret_reference" placeholder="Contoh: secret://psychotest/callback" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">SLA Hasil</label>
                            <input name="sla_result_hours" placeholder="Contoh: 24 jam" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Owner HR/IT</label>
                            <input name="owner_name" placeholder="Nama PIC integrasi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Koneksi</label>
                            <select name="connection_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="draft">Draft</option>
                                <option value="credential_ready">Credential Ready</option>
                                <option value="testing">Testing</option>
                                <option value="connected">Connected</option>
                                <option value="error">Error</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                    </div>
                    <textarea name="notes" rows="3" placeholder="Catatan kontrak vendor, batasan API, format callback, atau rencana go-live." class="mt-3 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    <label class="mt-3 flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Provider aktif
                    </label>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-plug-circle-check"></i>
                    Simpan Provider
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Paket Test</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Paket Psikotest',
                        'description' => 'Paket test adalah konfigurasi assessment yang nanti dipilih saat HR mengirim undangan psikotest ke kandidat.',
                        'steps' => [
                            'Pilih provider yang sudah dibuat.',
                            'Isi kategori assessment, level target, estimasi durasi, dan skala hasil.',
                            'Isi competency mapping agar hasil provider bisa dibaca dalam bahasa HR internal.',
                            'Aktifkan auto pull jika provider mendukung API callback atau result polling.',
                        ],
                        'notes' => [
                            'Candidate consent wajib untuk proses yang memproses data pribadi kandidat.',
                            'Status Active berarti paket boleh digunakan untuk invitation flow.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Dipakai oleh workflow undangan psikotest kandidat dan sinkron hasil di tahap berikutnya.</p>
            </div>
            <form action="{{ route('recruitment.psychotest-integrations.packages') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Paket</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Provider</label>
                            <select name="psychotest_provider_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih provider</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->provider_code }} - {{ $provider->provider_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Paket</label>
                            <input name="package_code" required placeholder="Contoh: PSY-PKG-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Paket</label>
                            <input name="package_name" required placeholder="Contoh: Staff Fit Assessment" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Klasifikasi Test</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</label>
                            <select name="assessment_category" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="personality">Personality</option>
                                <option value="cognitive">Cognitive</option>
                                <option value="integrity">Integrity</option>
                                <option value="leadership">Leadership</option>
                                <option value="technical">Technical</option>
                                <option value="culture_fit">Culture Fit</option>
                                <option value="complete_battery">Complete Battery</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Level Target</label>
                            <select name="target_role_level" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="all_level">Semua level</option>
                                <option value="operator">Operator</option>
                                <option value="staff">Staff</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="manager">Manager</option>
                                <option value="executive">Executive</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Estimasi Durasi</label>
                            <input type="number" name="estimated_minutes" value="60" min="5" max="480" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Competency Mapping</label>
                        <input name="competency_mapping" placeholder="Contoh: integritas, teamwork, problem solving" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Hasil & Status</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Skala Hasil</label>
                            <select name="result_scale" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="score_0_100">Score 0-100</option>
                                <option value="sten">STEN</option>
                                <option value="percentile">Percentile</option>
                                <option value="fit_recommendation">Fit Recommendation</option>
                                <option value="provider_report">Provider Report</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Interpretasi</label>
                            <select name="interpretation_method" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="provider_report">Report Provider</option>
                                <option value="hr_mapping">Mapping HR</option>
                                <option value="ai_summary_reviewed">AI Summary Reviewed</option>
                                <option value="manual_review">Manual Review</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Paket</label>
                            <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="draft">Draft</option>
                                <option value="testing">Testing</option>
                                <option value="active">Active</option>
                                <option value="retired">Retired</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="auto_pull_result" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            Auto pull hasil dari provider
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="requires_candidate_consent" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            Wajib consent kandidat
                        </label>
                    </div>
                    <textarea name="notes" rows="3" placeholder="Catatan paket, batas usia/norma, atau instruksi kandidat." class="mt-3 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-layer-group"></i>
                    Simpan Paket Test
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Provider & Paket Aktif</h2>
            <p class="text-sm text-gray-500">Ringkasan kesiapan integrasi sebelum workflow undangan psikotest kandidat dijalankan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Provider</th>
                        <th class="px-5 py-3">Koneksi</th>
                        <th class="px-5 py-3">Security</th>
                        <th class="px-5 py-3">Paket</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($providers as $provider)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $provider->provider_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $provider->provider_code }}</div>
                                <div class="text-xs text-gray-500">{{ $provider->company?->code ?: 'Semua perusahaan' }} / {{ $provider->owner_name ?: 'PIC belum diisi' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $provider->provider_type)) }}</div>
                                <div class="max-w-xs truncate text-xs text-gray-500">{{ $provider->base_url ?: 'Base URL belum diisi' }}</div>
                                <div class="text-xs text-gray-500">SLA: {{ $provider->sla_result_hours ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ strtoupper(str_replace('_', ' ', $provider->auth_method)) }}</div>
                                <div class="max-w-xs truncate text-xs text-gray-500">{{ $provider->credential_reference ?: 'Credential reference belum diisi' }}</div>
                                <div class="max-w-xs truncate text-xs text-gray-500">Webhook: {{ $provider->webhook_url ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-2xl font-semibold text-blue-600">{{ $provider->packages_count }}</div>
                                <div class="text-xs text-gray-500">Paket tersedia</div>
                                @foreach($provider->packages->take(2) as $package)
                                    <div class="mt-1 text-xs text-gray-600">{{ $package->package_code }} - {{ $package->package_name }}</div>
                                @endforeach
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusClass = [
                                        'connected' => 'bg-green-50 text-green-700',
                                        'testing' => 'bg-blue-50 text-blue-700',
                                        'credential_ready' => 'bg-amber-50 text-amber-700',
                                        'error' => 'bg-red-50 text-red-700',
                                        'disabled' => 'bg-gray-100 text-gray-600',
                                    ][$provider->connection_status] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ strtoupper(str_replace('_', ' ', $provider->connection_status)) }}</span>
                                <div class="mt-2 text-xs text-gray-500">{{ $provider->is_active ? 'Provider aktif' : 'Provider nonaktif' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada provider psikotest.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
