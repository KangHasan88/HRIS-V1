@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Output Borongan</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola tarif pekerjaan, volume output, nilai borongan, dan approval mandor untuk tenaga non-employee.</p>
        </div>
        <a href="{{ route('external-workforce.attendance') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-calendar-check"></i> Attendance & Mandays
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
                ['label' => 'Tarif Aktif', 'value' => $summary['rates'], 'color' => 'text-blue-600'],
                ['label' => 'Output Record', 'value' => $summary['outputs'], 'color' => 'text-blue-600'],
                ['label' => 'Qty Accepted', 'value' => number_format($summary['accepted_quantity'], 2, ',', '.'), 'color' => 'text-green-600'],
                ['label' => 'Ready Handoff', 'value' => $summary['ready_handoff'], 'color' => 'text-amber-600'],
                ['label' => 'Nilai Bruto', 'value' => 'Rp '.number_format($summary['gross_amount'], 0, ',', '.'), 'color' => 'text-gray-900'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Master Tarif Pekerjaan</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai tarif borongan',
                        'description' => 'Tarif dipakai sebagai dasar hitung output borongan. Satu pekerjaan bisa punya satuan berbeda seperti kg, meter, item, ritase, atau hektar.',
                        'steps' => [
                            'Pilih kontrak/vendor jika tarif mengikuti kontrak jasa tertentu.',
                            'Isi nama pekerjaan, satuan, dan tarif per satuan.',
                            'Isi quality rule agar mandor tahu standar output yang boleh diterima.',
                            'Tarif aktif akan muncul di form input output.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Contoh: panen per kg, packing per box, loading per rit, atau pekerjaan project per titik.</p>
            </div>
            <form action="{{ route('external-workforce.piecework.rates.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Tarif</label>
                        <input name="rate_code" required placeholder="Contoh: BOR-LOAD-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</label>
                        <select name="work_category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="borongan">Borongan</option>
                            <option value="output_based">Output based</option>
                            <option value="project_task">Project task</option>
                            <option value="daily_target">Target harian</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Pekerjaan</label>
                        <input name="work_name" required placeholder="Contoh: Loading barang ke truk" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kontrak Vendor</label>
                        <select name="external_workforce_contract_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Tanpa kontrak</option>
                            @foreach($contracts as $contract)
                                <option value="{{ $contract->id }}">{{ $contract->contract_number }} - {{ $contract->vendor?->vendor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Vendor</label>
                        <select name="external_workforce_vendor_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Opsional</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->vendor_code }} - {{ $vendor->vendor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Satuan</label>
                        <input name="unit_name" required value="unit" placeholder="kg / box / rit / meter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tarif per Satuan</label>
                        <input type="number" name="rate_per_unit" required min="0" placeholder="Contoh: 2500" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Minimum Output</label>
                        <input type="number" name="minimum_output" value="0" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                        <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="active">Active</option>
                            <option value="draft">Draft</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Quality Rule</label>
                        <input name="quality_rule" placeholder="Contoh: hanya qty lolos QC yang dibayar" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Tarif</label>
                        <textarea name="notes" rows="2" placeholder="Catatan kontrak, lokasi, atau pengecualian tarif." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-tags"></i> Simpan Tarif
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Input Output Borongan</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara input output borongan',
                        'description' => 'Output dicatat oleh HR/site admin dari laporan mandor atau vendor. Nilai dibayar dari accepted quantity dikali tarif per satuan.',
                        'steps' => [
                            'Pilih tarif pekerjaan dan worker jika output melekat ke orang tertentu.',
                            'Jika output per team, isi team name dan kosongkan worker.',
                            'Isi quantity total, accepted quantity, dan rejected quantity sesuai validasi mandor/QC.',
                            'Set approved jika mandor sudah menyetujui agar handoff siap.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Nilai bruto dihitung otomatis dari quantity accepted dikali tarif pekerjaan.</p>
            </div>
            <form action="{{ route('external-workforce.piecework.outputs.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tarif Pekerjaan</label>
                        <select name="external_workforce_piece_rate_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih tarif aktif</option>
                            @foreach($rates as $rate)
                                <option value="{{ $rate->id }}">{{ $rate->rate_code }} - {{ $rate->work_name }} / Rp {{ number_format((float) $rate->rate_per_unit, 0, ',', '.') }} per {{ $rate->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Output</label>
                        <input name="output_number" required placeholder="Contoh: OUT-BOR-099" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Kerja</label>
                        <input type="date" name="work_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Worker</label>
                        <select name="external_workforce_worker_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Output per team / tanpa worker</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->worker_number }} - {{ $worker->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Team / Kelompok</label>
                        <input name="team_name" placeholder="Contoh: Team Mandor A" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Qty Total</label>
                        <input type="number" name="quantity" required min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Qty Accepted</label>
                        <input type="number" name="accepted_quantity" min="0" step="0.01" placeholder="Kosong = ikut qty total" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Qty Rejected</label>
                        <input type="number" name="rejected_quantity" value="0" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Override Tarif</label>
                        <input type="number" name="rate_per_unit" value="0" min="0" placeholder="0 = ikut master tarif" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mandor / Approver</label>
                        <input name="foreman_name" placeholder="Nama mandor/supervisor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                        <select name="approval_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bukti / Referensi</label>
                        <input name="evidence_reference" placeholder="Nomor timesheet, foto QC, atau laporan vendor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Output</label>
                        <textarea name="notes" rows="2" placeholder="Catatan hasil kerja, reject, atau instruksi invoice/payroll." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-clipboard-check"></i> Simpan Output
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Daftar Output Borongan</h2>
                <p class="text-sm text-gray-500">Output approved siap menjadi dasar invoice vendor atau payroll internal.</p>
            </div>
            <form method="GET" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="approval_status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua approval</option>
                    @foreach(['submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['approval_status'] === $value)>{{ $label }}</option>
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
                        <th class="px-5 py-3">Output</th>
                        <th class="px-5 py-3">Pekerjaan</th>
                        <th class="px-5 py-3">Worker / Team</th>
                        <th class="px-5 py-3">Quantity</th>
                        <th class="px-5 py-3">Nilai</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($outputs as $output)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs text-gray-500">{{ $output->output_number }}</div>
                                <div class="font-semibold text-gray-900">{{ $output->work_date?->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $output->evidence_reference ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $output->rate?->work_name ?: '-' }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $output->rate?->rate_code ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $output->vendor?->vendor_name ?: 'Tanpa vendor' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ $output->worker?->full_name ?: ($output->team_name ?: '-') }}</div>
                                <div class="text-xs text-gray-500">{{ $output->worker?->worker_number ?: 'Team output' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>Total: {{ number_format((float) $output->quantity, 2, ',', '.') }}</div>
                                <div class="text-xs text-green-700">Accepted: {{ number_format((float) $output->accepted_quantity, 2, ',', '.') }}</div>
                                <div class="text-xs text-red-700">Rejected: {{ number_format((float) $output->rejected_quantity, 2, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div class="font-semibold text-gray-900">Rp {{ number_format((float) $output->gross_amount, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Rp {{ number_format((float) $output->rate_per_unit, 0, ',', '.') }} / unit</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full {{ $output->approval_status === 'approved' ? 'bg-green-50 text-green-700' : ($output->approval_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }} px-2 py-1 text-xs font-semibold">
                                    {{ ucfirst($output->approval_status) }}
                                </span>
                                <div class="mt-2 text-xs text-gray-500">Mandor: {{ $output->foreman_name ?: '-' }}</div>
                                <div class="mt-1 text-xs text-gray-500">Handoff: {{ str_replace('_', ' ', $output->handoff_status) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if(!in_array($output->approval_status, ['approved', 'rejected'], true))
                                    <form action="{{ route('external-workforce.piecework.outputs.review', $output) }}" method="POST" class="flex flex-col gap-2">
                                        @csrf
                                        <input type="hidden" name="foreman_name" value="{{ auth()->user()->name }}">
                                        <button name="decision" value="approved" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Approve</button>
                                        <button name="decision" value="rejected" class="rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">Reject</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Reviewed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500">Belum ada output borongan untuk filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
