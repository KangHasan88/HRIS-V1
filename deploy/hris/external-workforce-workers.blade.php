@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Worker Non-Employee</h1>
            <p class="mt-1 text-sm text-gray-500">Catat pekerja outsource, harian, borongan, freelance, konsultan, dan project worker tanpa mencampur data employee internal.</p>
        </div>
        <a href="{{ route('external-workforce.vendors') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-people-carry-box"></i> Vendor & Kontrak
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
                ['label' => 'Total Worker', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Vendor Invoice', 'value' => $summary['vendor_invoice'], 'color' => 'text-blue-600'],
                ['label' => 'Payroll Internal', 'value' => $summary['internal_payroll'], 'color' => 'text-amber-600'],
                ['label' => 'Dokumen Kurang', 'value' => $summary['document_incomplete'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Worker</h2>
                @include('partials.form-help', [
                    'title' => 'Cara pakai master worker non-employee',
                    'description' => 'Form ini dipakai untuk mencatat orang non-employee yang bekerja di area perusahaan, baik dibayar vendor, payroll internal, atau hanya dicatat untuk akses operasional.',
                    'steps' => [
                        'Pilih vendor/kontrak jika worker berasal dari outsourcing atau partner borongan.',
                        'Isi tipe worker agar proses berikutnya tahu apakah dihitung mandays, output borongan, atau invoice vendor.',
                        'Tentukan payment handling: vendor invoice, payroll internal, atau operational only.',
                        'Centang dokumen KTP, kontrak vendor, HSE induction, dan akses area untuk kontrol compliance.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Data ini menjadi dasar attendance harian, output borongan, approval mandor/supervisor, dan rekonsiliasi invoice vendor.</p>
        </div>

        <form action="{{ route('external-workforce.workers.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="grid gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Worker</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Worker</label>
                            <input name="worker_number" required placeholder="Contoh: EXT-0001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Lengkap</label>
                            <input name="full_name" required placeholder="Nama worker" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Worker</label>
                            <select name="worker_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="outsource">Outsource</option>
                                <option value="daily_worker">Harian</option>
                                <option value="piece_rate">Borongan</option>
                                <option value="freelance">Freelance</option>
                                <option value="consultant">Konsultan</option>
                                <option value="project_worker">Project worker</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="active">Active</option>
                                <option value="candidate">Candidate</option>
                                <option value="inactive">Inactive</option>
                                <option value="blocked">Blocked</option>
                                <option value="ended">Ended</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">No. Identitas</label>
                            <input name="identity_number" placeholder="KTP / passport" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor HP</label>
                            <input name="phone" placeholder="Nomor HP worker" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posisi / Pekerjaan</label>
                            <input name="position_title" placeholder="Helper, mandor, operator" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Supervisor</label>
                            <input name="supervisor_name" placeholder="Atasan / mandor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Relasi Vendor & Scope</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kontrak Vendor</label>
                            <select name="external_workforce_contract_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Tanpa kontrak vendor</option>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->id }}">{{ $contract->contract_number }} - {{ $contract->vendor?->vendor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Vendor</label>
                            <select name="external_workforce_vendor_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Opsional jika sudah pilih kontrak</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->vendor_code }} - {{ $vendor->vendor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Ikuti kontrak</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                            <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Ikuti kontrak</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cost Center</label>
                            <select name="cost_center_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Ikuti kontrak</option>
                                @foreach($costCenters as $costCenter)
                                    <option value="{{ $costCenter->id }}">{{ $costCenter->code }} - {{ $costCenter->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Payroll / Invoice</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Kerja</label>
                            <input type="date" name="start_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Kerja</label>
                            <input type="date" name="end_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Handling Pembayaran</label>
                            <select name="payment_handling" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="vendor_invoice">Vendor invoice</option>
                                <option value="internal_payroll">Payroll internal</option>
                                <option value="operational_only">Operational only</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tarif Harian</label>
                            <input type="number" name="daily_rate" value="0" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tarif Borongan</label>
                            <input type="number" name="piece_rate" value="0" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Dokumen & Akses</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        @foreach([
                            'has_ktp' => 'KTP tersedia',
                            'has_vendor_contract' => 'Kontrak vendor tersedia',
                            'has_hse_induction' => 'HSE induction selesai',
                            'has_area_access' => 'Akses area aktif',
                        ] as $name => $label)
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="{{ $name }}" value="1" class="rounded border-gray-300 text-blue-600">
                                {{ $label }}
                            </label>
                        @endforeach
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Area Akses</label>
                            <input name="access_area" placeholder="Site / gudang / project area" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akses Berlaku Sampai</label>
                            <input type="date" name="access_valid_until" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                            <textarea name="notes" rows="2" placeholder="Catatan operasional, risiko, atau instruksi payroll/vendor invoice." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-user-plus"></i> Simpan Worker
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Daftar Worker Non-Employee</h2>
                <p class="text-sm text-gray-500">Filter worker berdasarkan tipe dan status untuk export/rekap berikutnya.</p>
            </div>
            <form method="GET" class="flex flex-col gap-2 sm:flex-row">
                <select name="worker_type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua tipe</option>
                    @foreach(['outsource' => 'Outsource', 'daily_worker' => 'Harian', 'piece_rate' => 'Borongan', 'freelance' => 'Freelance', 'consultant' => 'Konsultan', 'project_worker' => 'Project worker'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['worker_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua status</option>
                    @foreach(['active' => 'Active', 'candidate' => 'Candidate', 'inactive' => 'Inactive', 'blocked' => 'Blocked', 'ended' => 'Ended'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Worker</th>
                        <th class="px-5 py-3">Vendor / Kontrak</th>
                        <th class="px-5 py-3">Scope</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3">Dokumen</th>
                        <th class="px-5 py-3">Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($workers as $worker)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $worker->full_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $worker->worker_number }}</div>
                                <span class="mt-2 inline-flex rounded-full {{ $worker->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700' }} px-2 py-1 text-xs font-semibold">{{ ucfirst($worker->status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $worker->vendor?->vendor_name ?: '-' }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $worker->contract?->contract_number ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ str_replace('_', ' ', $worker->worker_type) }}</div>
                                <div class="text-xs text-gray-500">{{ $worker->position_title ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $worker->company?->code ?: '-' }} / {{ $worker->branch?->code ?: '-' }} / {{ $worker->costCenter?->code ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ str_replace('_', ' ', $worker->payment_handling) }}</div>
                                <div class="text-xs text-gray-500">Harian: Rp {{ number_format((float) $worker->daily_rate, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Borongan: Rp {{ number_format((float) $worker->piece_rate, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @php($docs = $worker->document_status ?? [])
                                <div class="flex flex-wrap gap-1">
                                    @foreach(['ktp' => 'KTP', 'vendor_contract' => 'Kontrak', 'hse_induction' => 'HSE', 'area_access' => 'Akses'] as $key => $label)
                                        <span class="rounded-full {{ !empty($docs[$key]) ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }} px-2 py-1 text-xs font-semibold">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ $worker->access_scope['area'] ?? '-' }}</div>
                                <div class="text-xs text-gray-500">Valid: {{ $worker->access_scope['valid_until'] ?? '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada worker non-employee.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
