@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Vendor Outsourcing</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola vendor penyedia tenaga kerja, kontrak jasa SDM, scope site, SLA, dan dokumen legal.</p>
        </div>
        <a href="{{ route('hris.employees') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-users"></i> Data Karyawan
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Vendor', 'value' => $summary['vendors'], 'color' => 'text-blue-600'],
                ['label' => 'Vendor Aktif', 'value' => $summary['active_vendors'], 'color' => 'text-green-600'],
                ['label' => 'Kontrak Aktif', 'value' => $summary['active_contracts'], 'color' => 'text-blue-600'],
                ['label' => 'Akan Expired', 'value' => $summary['expiring_contracts'], 'color' => 'text-amber-600'],
                ['label' => 'Dokumen Kurang', 'value' => $summary['missing_documents'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Vendor</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai master vendor outsourcing',
                        'description' => 'Form ini dipakai untuk mencatat perusahaan penyedia tenaga kerja atau partner borongan sebelum kontraknya dibuat.',
                        'steps' => [
                            'Isi identitas vendor, NPWP/NIB, PIC, dan kontak vendor.',
                            'Pilih tipe vendor sesuai model kerja sama: outsourcing, harian, borongan, konsultan, atau lainnya.',
                            'Centang dokumen legal yang sudah diterima agar HR tahu vendor mana yang masih kurang dokumen.',
                            'Set status Active jika vendor sudah boleh dipakai di kontrak jasa SDM.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Vendor adalah perusahaan/partner penyedia SDM, bukan pekerja per orang.</p>
            </div>

            <form action="{{ route('external-workforce.vendors.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Vendor</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Vendor</label>
                            <input name="vendor_code" required placeholder="Contoh: VND-OS-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Vendor</label>
                            <input name="vendor_name" required placeholder="Contoh: PT Mitra Tenaga Site" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Vendor</label>
                            <select name="vendor_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="outsourcing">Outsourcing manpower</option>
                                <option value="daily_worker_supplier">Supplier tenaga harian</option>
                                <option value="piece_rate_partner">Partner borongan</option>
                                <option value="consultant_agency">Agency konsultan/freelance</option>
                                <option value="security_cleaning">Security / cleaning</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Vendor</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="active">Active</option>
                                <option value="prospect">Prospect</option>
                                <option value="paused">Paused</option>
                                <option value="ended">Ended</option>
                                <option value="blacklisted">Blacklisted</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Legal & PIC</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">NPWP</label>
                            <input name="npwp" placeholder="Nomor NPWP vendor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">NIB</label>
                            <input name="nib" placeholder="Nomor NIB vendor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">PIC Vendor</label>
                            <input name="pic_name" placeholder="Nama PIC vendor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kontak PIC</label>
                            <input name="pic_phone" placeholder="Nomor HP PIC" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Email PIC</label>
                            <input name="pic_email" type="email" placeholder="email@vendor.co.id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alamat Vendor</label>
                            <textarea name="address" rows="2" placeholder="Alamat kantor vendor." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        @foreach([
                            'has_npwp' => 'Dokumen NPWP diterima',
                            'has_nib' => 'Dokumen NIB diterima',
                            'has_service_agreement' => 'Service agreement tersedia',
                            'has_hse_commitment' => 'Komitmen HSE tersedia',
                        ] as $name => $label)
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="{{ $name }}" value="1" class="rounded border-gray-300 text-blue-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Vendor</label>
                        <textarea name="notes" rows="2" placeholder="Catatan legal, performance vendor, atau risiko kerja sama." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Vendor
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Kontrak Jasa SDM</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai kontrak jasa SDM',
                        'description' => 'Form ini mengikat vendor ke company/site/cost center, periode kontrak, jenis tenaga, SLA, tarif, dan pembagian tanggung jawab compliance.',
                        'steps' => [
                            'Pilih vendor yang sudah dibuat.',
                            'Isi nomor kontrak, periode, scope tenaga, kuota headcount, dan tarif dasar.',
                            'Tentukan lokasi/cost center supaya tagihan vendor dan mandays nanti jelas.',
                            'Tentukan siapa yang bertanggung jawab atas pajak, BPJS, HSE, dan akses area.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Satu vendor bisa punya lebih dari satu kontrak sesuai lokasi, periode, atau model pekerjaan.</p>
            </div>

            <form action="{{ route('external-workforce.contracts.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Kontrak</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Vendor</label>
                            <select name="external_workforce_vendor_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->vendor_code }} - {{ $vendor->vendor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Kontrak</label>
                            <input name="contract_number" required placeholder="Contoh: OS/BMP/2026/001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Judul Kontrak</label>
                            <input name="contract_title" required placeholder="Pengadaan tenaga helper site" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Scope Tenaga</label>
                            <select name="workforce_scope" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="outsourced_manpower">Outsourced manpower</option>
                                <option value="daily_worker">Tenaga harian</option>
                                <option value="piece_rate">Borongan / output based</option>
                                <option value="freelance_consultant">Freelance / consultant</option>
                                <option value="mixed">Campuran</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Tenaga</label>
                            <input name="worker_type_scope" required placeholder="Contoh: Helper, security, operator, cleaning" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Kontrak</label>
                            <input type="date" name="start_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Kontrak</label>
                            <input type="date" name="end_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Reminder Renewal</label>
                            <input type="date" name="renewal_reminder_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Kontrak</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="expiring">Expiring</option>
                                <option value="ended">Ended</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Scope Operasional & Tarif</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Semua perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                            <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Semua cabang/site</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cost Center</label>
                            <select name="cost_center_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Opsional</option>
                                @foreach($costCenters as $costCenter)
                                    <option value="{{ $costCenter->id }}">{{ $costCenter->code }} - {{ $costCenter->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kuota Headcount</label>
                            <input type="number" name="headcount_quota" value="0" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Service Fee Bulanan</label>
                            <input type="number" name="monthly_service_fee" value="0" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tarif Harian</label>
                            <input type="number" name="daily_rate" value="0" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tarif Lembur</label>
                            <input type="number" name="overtime_rate" value="0" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dokumen Kontrak</label>
                            <input name="document_reference" placeholder="Nomor file / URL dokumen" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">SLA</label>
                            <input name="sla_summary" placeholder="Contoh: replacement maksimal 2 hari, absensi harian, invoice bulanan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Lokasi</label>
                            <input name="location_scope_note" placeholder="Area kerja, site, departemen, atau batasan akses." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Tanggung Jawab Compliance</h3>
                    <p class="mt-1 text-xs text-gray-500">Dipakai untuk membedakan apakah pajak, BPJS, HSE, dan akses area menjadi tanggung jawab vendor, perusahaan, atau shared.</p>
                    <div class="mt-3 grid gap-3 md:grid-cols-4">
                        @foreach([
                            'tax_responsibility' => 'Pajak',
                            'bpjs_responsibility' => 'BPJS',
                            'hse_responsibility' => 'HSE',
                            'access_responsibility' => 'Akses Area',
                        ] as $name => $label)
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</label>
                                <select name="{{ $name }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="vendor">Vendor</option>
                                    <option value="company">Perusahaan</option>
                                    <option value="shared">Shared</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Kontrak</label>
                        <textarea name="notes" rows="2" placeholder="Catatan komersial, risiko kontrak, klausul khusus, atau arahan finance." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-file-contract"></i> Simpan Kontrak
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Vendor</h2>
            <p class="text-sm text-gray-500">Monitoring vendor aktif, dokumen legal, dan jumlah kontrak.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Vendor</th>
                        <th class="px-5 py-3">PIC</th>
                        <th class="px-5 py-3">Scope</th>
                        <th class="px-5 py-3">Legal</th>
                        <th class="px-5 py-3">Kontrak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($vendors as $vendor)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $vendor->vendor_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $vendor->vendor_code }}</div>
                                <span class="mt-2 inline-flex rounded-full {{ $vendor->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700' }} px-2 py-1 text-xs font-semibold">{{ ucfirst($vendor->status) }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div class="font-semibold text-gray-900">{{ $vendor->pic_name ?: '-' }}</div>
                                <div class="text-xs">{{ $vendor->pic_phone ?: '-' }}</div>
                                <div class="text-xs">{{ $vendor->pic_email ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ str_replace('_', ' ', $vendor->vendor_type) }}</div>
                                <div class="text-xs text-gray-500">{{ $vendor->company?->code ?: 'Semua company' }} / {{ $vendor->branch?->code ?: 'Semua site' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @php($legal = $vendor->compliance_snapshot ?? [])
                                <div class="flex flex-wrap gap-1">
                                    @foreach(['npwp' => 'NPWP', 'nib' => 'NIB', 'service_agreement' => 'Agreement', 'hse_commitment' => 'HSE'] as $key => $label)
                                        <span class="rounded-full {{ !empty($legal[$key]) ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }} px-2 py-1 text-xs font-semibold">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-2xl font-semibold text-blue-600">{{ $vendor->contracts->count() }}</div>
                                <div class="text-xs text-gray-500">kontrak</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada vendor outsourcing.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Kontrak Jasa SDM</h2>
            <p class="text-sm text-gray-500">Kontrak ini menjadi dasar worker non-employee, mandays, borongan, approval, dan invoice vendor di backlog berikutnya.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Kontrak</th>
                        <th class="px-5 py-3">Vendor</th>
                        <th class="px-5 py-3">Scope</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Tarif</th>
                        <th class="px-5 py-3">Compliance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contracts as $contract)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $contract->contract_title }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $contract->contract_number }}</div>
                                <span class="mt-2 inline-flex rounded-full {{ $contract->status === 'active' ? 'bg-green-50 text-green-700' : ($contract->status === 'expiring' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-700') }} px-2 py-1 text-xs font-semibold">{{ ucfirst($contract->status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $contract->vendor?->vendor_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $contract->vendor?->vendor_code }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ str_replace('_', ' ', $contract->workforce_scope) }}</div>
                                <div class="text-xs text-gray-500">{{ $contract->worker_type_scope }}</div>
                                <div class="text-xs text-gray-500">{{ $contract->company?->code ?: '-' }} / {{ $contract->branch?->code ?: '-' }} / {{ $contract->costCenter?->code ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ $contract->start_date?->format('d M Y') }} - {{ $contract->end_date?->format('d M Y') ?: 'Tidak terbatas' }}</div>
                                <div class="text-xs text-gray-500">Reminder: {{ $contract->renewal_reminder_date?->format('d M Y') ?: '-' }}</div>
                                <div class="text-xs text-gray-500">Kuota: {{ $contract->headcount_quota }} orang</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>Fee: Rp {{ number_format((float) $contract->monthly_service_fee, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Harian: Rp {{ number_format((float) $contract->daily_rate, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Lembur: Rp {{ number_format((float) $contract->overtime_rate, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @php($matrix = $contract->responsibility_matrix ?? [])
                                <div class="grid gap-1 text-xs text-gray-600">
                                    <div>Pajak: <span class="font-semibold text-gray-900">{{ ucfirst($matrix['tax'] ?? '-') }}</span></div>
                                    <div>BPJS: <span class="font-semibold text-gray-900">{{ ucfirst($matrix['bpjs'] ?? '-') }}</span></div>
                                    <div>HSE: <span class="font-semibold text-gray-900">{{ ucfirst($matrix['hse'] ?? '-') }}</span></div>
                                    <div>Akses: <span class="font-semibold text-gray-900">{{ ucfirst($matrix['access_area'] ?? '-') }}</span></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada kontrak jasa SDM.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
