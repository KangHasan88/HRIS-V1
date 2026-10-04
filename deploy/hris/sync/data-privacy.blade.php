@extends('layouts.sidebar')

@section('title', 'Data Privacy Control')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Data Privacy Control</h1>
            <p class="mt-1 text-sm text-gray-500">Kontrol masking, consent, retensi, dan legal hold untuk data sensitif HRIS.</p>
        </div>
        <a href="{{ route('settings.audit-trail') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-clipboard-list"></i>
            Audit Trail
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Policy', 'value' => $summary['policies'], 'color' => 'text-blue-600', 'hint' => 'Semua scope'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600', 'hint' => 'Enforced'],
                ['label' => 'Butuh Consent', 'value' => $summary['consent_required'], 'color' => 'text-amber-600', 'hint' => 'Policy'],
                ['label' => 'Consent', 'value' => $summary['consents'], 'color' => 'text-blue-600', 'hint' => 'Register'],
                ['label' => 'Retensi', 'value' => $summary['retention_jobs'], 'color' => 'text-gray-900', 'hint' => 'Job'],
                ['label' => 'Legal Hold', 'value' => $summary['legal_hold'], 'color' => 'text-red-600', 'hint' => 'Ditahan'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Buat Policy Privasi</h2>
                    <p class="text-sm text-gray-500">Tentukan field yang dimasking, role yang dibatasi, consent, dan masa retensi.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">Platform</span>
            </div>
            <form method="POST" action="{{ route('settings.data-privacy.policies') }}" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Policy</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <label class="space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Policy</span>
                            <input name="policy_code" required placeholder="Contoh: PRIV-EMP-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </label>
                        <label class="space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Policy</span>
                            <input name="policy_name" required placeholder="Contoh: Masking Data Karyawan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </label>
                        <label class="space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Company</span>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Global semua company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Domain Data</span>
                            <select name="data_domain" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                @foreach($fieldCatalog as $domain => $fields)
                                    <option value="{{ $domain }}">{{ ucwords(str_replace('_', ' ', $domain)) }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Masking & Akses</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <label class="space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Mode Masking</span>
                            <select name="masking_mode" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="partial">Partial, contoh ********1234</option>
                                <option value="full">Full masking</option>
                                <option value="hash_only">Hash only untuk audit</option>
                                <option value="role_based">Role-based masking</option>
                            </select>
                        </label>
                        <label class="space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Retensi Tahun</span>
                            <input type="number" name="retention_years" min="1" max="30" value="5" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </label>
                        <label class="space-y-1">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</span>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </label>
                    </div>
                    <div class="mt-4 grid gap-3 lg:grid-cols-2">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Field Sensitif</div>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                @foreach(['nik' => 'NIK', 'npwp' => 'NPWP', 'bank_account' => 'Rekening Bank', 'salary' => 'Gaji', 'mobile_location' => 'Lokasi Mobile', 'psychotest_result' => 'Hasil Psikotest'] as $value => $label)
                                    <label class="flex items-center gap-2 text-sm text-gray-600">
                                        <input type="checkbox" name="masked_fields[]" value="{{ $value }}" class="rounded border-gray-300 text-blue-600">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Role Dibatasi</div>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                @foreach($roleCatalog as $role)
                                    <label class="flex items-center gap-2 text-sm text-gray-600">
                                        <input type="checkbox" name="restricted_roles[]" value="{{ $role }}" class="rounded border-gray-300 text-blue-600">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                            <input type="checkbox" name="consent_required" value="1" class="rounded border-gray-300 text-blue-600">
                            Wajib consent dari pemilik data
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                            <input type="checkbox" name="legal_hold_enabled" value="1" class="rounded border-gray-300 text-blue-600">
                            Bisa ditahan legal hold
                        </label>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <input name="legal_basis" required placeholder="Dasar hukum/kepentingan, contoh: Kepatuhan ketenagakerjaan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="notes" placeholder="Catatan policy atau pengecualian akses" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-shield-halved"></i>
                        Simpan Policy
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Preview Masking</h2>
                <p class="mt-1 text-sm text-gray-500">Contoh tampilan data sensitif setelah policy aktif.</p>
                <div class="mt-4 space-y-3">
                    @foreach([
                        ['label' => 'NIK', 'raw' => '3174011201900001', 'masked' => '************0001'],
                        ['label' => 'NPWP', 'raw' => '09.123.456.7-012.000', 'masked' => '**************2.000'],
                        ['label' => 'Rekening', 'raw' => '1234567890123', 'masked' => '*********0123'],
                        ['label' => 'Gaji', 'raw' => 'Rp 15.000.000', 'masked' => 'Role restricted'],
                    ] as $sample)
                        <div class="rounded-xl bg-gray-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $sample['label'] }}</div>
                            <div class="mt-1 font-mono text-sm text-gray-900">{{ $sample['masked'] }}</div>
                            <div class="text-xs text-gray-400">Raw disimpan terenkripsi / terbatas.</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Field Catalog</h2>
                <p class="mt-1 text-sm text-gray-500">Referensi field sensitif per domain.</p>
                <div class="mt-4 space-y-3">
                    @foreach($fieldCatalog as $domain => $fields)
                        <div class="rounded-xl border border-gray-100 p-3">
                            <div class="text-sm font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $domain)) }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ implode(', ', $fields) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Catat Consent</h2>
                <p class="text-sm text-gray-500">Dipakai untuk kandidat, karyawan, vendor worker, atau admin user.</p>
            </div>
            <form method="POST" action="{{ route('settings.data-privacy.consents') }}" class="mt-5 space-y-3">
                @csrf
                <select name="platform_data_privacy_policy_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Tanpa link policy</option>
                    @foreach($policies as $policy)
                        <option value="{{ $policy->id }}">{{ $policy->policy_code }} - {{ $policy->policy_name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 md:grid-cols-2">
                    <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Global</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select name="subject_type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="employee">Karyawan</option>
                        <option value="candidate">Kandidat</option>
                        <option value="vendor_worker">Tenaga outsourcing/harian</option>
                        <option value="admin_user">Admin user</option>
                    </select>
                    <input name="subject_reference" required placeholder="NIK / nomor kandidat / worker id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="subject_name" placeholder="Nama pemilik data" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="consent_channel" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="web">Web</option>
                        <option value="mobile_webview">Mobile WebView</option>
                        <option value="paper">Form kertas</option>
                        <option value="email">Email</option>
                        <option value="api">API</option>
                    </select>
                    <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="granted">Granted</option>
                        <option value="pending">Pending</option>
                        <option value="revoked">Revoked</option>
                        <option value="expired">Expired</option>
                    </select>
                    <input type="date" name="granted_at" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input type="date" name="expires_at" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <input name="consent_purpose" required placeholder="Tujuan consent, contoh: proses payroll dan administrasi HR" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <textarea name="notes" rows="3" placeholder="Catatan evidence consent, link dokumen, atau pengecualian." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-user-check mr-2"></i>Simpan Consent</button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Buat Retention Job</h2>
                <p class="text-sm text-gray-500">Jadwalkan archive/anonymize/delete dengan legal hold dan approval.</p>
            </div>
            <form method="POST" action="{{ route('settings.data-privacy.retention-jobs') }}" class="mt-5 space-y-3">
                @csrf
                <select name="platform_data_privacy_policy_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Tanpa link policy</option>
                    @foreach($policies as $policy)
                        <option value="{{ $policy->id }}">{{ $policy->policy_code }} - {{ $policy->policy_name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 md:grid-cols-2">
                    <input name="job_number" required placeholder="Contoh: RET-EMP-001" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Global</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select name="data_domain" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @foreach($fieldCatalog as $domain => $fields)
                            <option value="{{ $domain }}">{{ ucwords(str_replace('_', ' ', $domain)) }}</option>
                        @endforeach
                    </select>
                    <input name="scope_reference" placeholder="Scope, contoh: ex-employee before 2021" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input type="date" name="cutoff_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="action_type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="archive">Archive</option>
                        <option value="anonymize">Anonymize</option>
                        <option value="delete">Delete</option>
                        <option value="legal_hold_review">Legal hold review</option>
                    </select>
                    <input type="number" name="estimated_records" min="0" value="0" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="draft">Draft</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="approved">Approved</option>
                        <option value="blocked">Blocked</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                    <input type="checkbox" name="legal_hold" value="1" class="rounded border-gray-300 text-blue-600">
                    Jangan hapus/anonymize karena sedang legal hold
                </label>
                <input type="datetime-local" name="scheduled_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <textarea name="notes" rows="3" placeholder="Catatan approval, risiko, atau instruksi legal/compliance." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-box-archive mr-2"></i>Simpan Retensi</button>
                </div>
            </form>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Policy & Retensi Terbaru</h2>
            <p class="text-sm text-gray-500">Ringkasan kontrol aktif dan job retensi terakhir.</p>
        </div>
        <div class="grid gap-0 lg:grid-cols-2">
            <div class="overflow-x-auto border-b border-gray-100 lg:border-b-0 lg:border-r">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Policy</th>
                            <th class="px-5 py-3">Masking</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($policies as $policy)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $policy->policy_code }}</div>
                                    <div class="text-xs text-gray-500">{{ $policy->policy_name }} / {{ $policy->company?->code ?? 'Global' }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500">{{ implode(', ', $policy->masked_fields ?? []) ?: '-' }}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($policy->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500">Belum ada policy privasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Job</th>
                            <th class="px-5 py-3">Aksi</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($retentionJobs as $job)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $job->job_number }}</div>
                                    <div class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $job->data_domain)) }} / cutoff {{ $job->cutoff_date?->format('d M Y') }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $job->action_type)) }} - {{ $job->estimated_records }} record</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $job->legal_hold ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-700' }} px-2 py-1 text-xs font-semibold">{{ $job->legal_hold ? 'Legal hold' : ucfirst($job->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500">Belum ada retention job.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
