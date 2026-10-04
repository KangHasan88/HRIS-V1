@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Rekonsiliasi Tagihan Vendor</h1>
            <p class="mt-1 text-sm text-gray-500">Cocokkan invoice vendor dengan data mandays dan output borongan yang sudah approved.</p>
        </div>
        <a href="{{ route('external-workforce.piecework') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-clipboard-check"></i> Output Borongan
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
                ['label' => 'Invoice', 'value' => $summary['invoices'], 'color' => 'text-blue-600'],
                ['label' => 'Nilai Sistem', 'value' => 'Rp '.number_format($summary['system_amount'], 0, ',', '.'), 'color' => 'text-green-600'],
                ['label' => 'Klaim Vendor', 'value' => 'Rp '.number_format($summary['claimed_amount'], 0, ',', '.'), 'color' => 'text-blue-600'],
                ['label' => 'Variance', 'value' => 'Rp '.number_format($summary['variance_amount'], 0, ',', '.'), 'color' => 'text-amber-600'],
                ['label' => 'Ready Payment', 'value' => $summary['ready_payment'], 'color' => 'text-gray-900'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Buat Invoice Rekonsiliasi</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai rekonsiliasi tagihan vendor',
                        'description' => 'Buat header invoice vendor, lalu tambahkan line dari mandays atau output borongan approved. Sistem menghitung variance antara nilai sistem dan klaim vendor.',
                        'steps' => [
                            'Pilih vendor dan kontrak jasa SDM jika invoice mengacu kontrak tertentu.',
                            'Isi periode invoice, nomor internal, dan nomor invoice vendor.',
                            'Tambahkan line dari sumber mandays/output atau manual adjustment.',
                            'Review invoice: approved membuat payment status menjadi ready.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Header invoice akan menjadi container rekonsiliasi sebelum payable.</p>
            </div>
            <form action="{{ route('external-workforce.vendor-invoices.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Vendor</label>
                        <select name="external_workforce_vendor_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->vendor_code }} - {{ $vendor->vendor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kontrak</label>
                        <select name="external_workforce_contract_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Opsional</option>
                            @foreach($vendors->flatMap->contracts as $contract)
                                <option value="{{ $contract->id }}">{{ $contract->contract_number }} - {{ $contract->contract_title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Invoice Internal</label>
                        <input name="invoice_number" required placeholder="Contoh: EXT-INV-100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Invoice Vendor</label>
                        <input name="vendor_invoice_number" placeholder="Nomor invoice dari vendor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode Mulai</label>
                        <input type="date" name="period_start" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode Akhir</label>
                        <input type="date" name="period_end" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Invoice</label>
                        <input type="date" name="invoice_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jatuh Tempo</label>
                        <input type="date" name="due_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Scope Sumber</label>
                        <select name="source_scope" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="mixed">Mixed</option>
                            <option value="mandays">Mandays</option>
                            <option value="piecework">Output borongan</option>
                            <option value="manual_adjustment">Manual adjustment</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Pajak / Adjustment</label>
                        <input type="number" name="tax_amount" value="0" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan invoice, dokumen pendukung, atau arahan finance." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-file-invoice-dollar"></i> Simpan Invoice
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Line Rekonsiliasi</h2>
                <p class="text-sm text-gray-500">Pilih invoice dan sumber approved. Klaim vendor dibandingkan dengan nilai sistem.</p>
            </div>
            <form action="{{ $invoices->first() ? route('external-workforce.vendor-invoices.lines.store', $invoices->first()) : '#' }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-800">Untuk tambah line, buka invoice yang sudah dibuat pada tabel di bawah. Form ini menampilkan sumber available untuk periode filter.</div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber Mandays Ready</label>
                        <select class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" disabled>
                            <option>{{ $attendanceSources->count() }} record mandays available</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber Output Ready</label>
                        <select class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" disabled>
                            <option>{{ $pieceworkSources->count() }} record output available</option>
                        </select>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Daftar Invoice Vendor</h2>
                <p class="text-sm text-gray-500">Detail line, variance, dan approval payable.</p>
            </div>
            <form method="GET" class="grid gap-2 sm:grid-cols-3">
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </form>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($invoices as $invoice)
                <div class="p-5">
                    <div class="grid gap-4 xl:grid-cols-[1fr_420px]">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-gray-900">{{ $invoice->invoice_number }}</h3>
                                <span class="rounded-full {{ $invoice->reconciliation_status === 'matched' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }} px-2 py-1 text-xs font-semibold">{{ str_replace('_', ' ', ucfirst($invoice->reconciliation_status)) }}</span>
                                <span class="rounded-full {{ $invoice->payment_status === 'ready' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700' }} px-2 py-1 text-xs font-semibold">{{ str_replace('_', ' ', ucfirst($invoice->payment_status)) }}</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-500">{{ $invoice->vendor?->vendor_name }} / {{ $invoice->vendor_invoice_number ?: 'No vendor invoice belum diisi' }}</div>
                            <div class="mt-3 grid gap-3 text-sm md:grid-cols-4">
                                <div><span class="text-gray-400">Periode</span><br><span class="font-semibold text-gray-900">{{ $invoice->period_start?->format('d/m/Y') }} - {{ $invoice->period_end?->format('d/m/Y') }}</span></div>
                                <div><span class="text-gray-400">Sistem</span><br><span class="font-semibold text-gray-900">Rp {{ number_format((float) $invoice->system_amount, 0, ',', '.') }}</span></div>
                                <div><span class="text-gray-400">Klaim</span><br><span class="font-semibold text-gray-900">Rp {{ number_format((float) $invoice->claimed_amount, 0, ',', '.') }}</span></div>
                                <div><span class="text-gray-400">Variance</span><br><span class="font-semibold {{ (float) $invoice->variance_amount === 0.0 ? 'text-green-700' : 'text-amber-700' }}">Rp {{ number_format((float) $invoice->variance_amount, 0, ',', '.') }}</span></div>
                            </div>

                            <div class="mt-4 overflow-x-auto rounded-xl border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3">Source</th>
                                            <th class="px-4 py-3">Deskripsi</th>
                                            <th class="px-4 py-3">Qty</th>
                                            <th class="px-4 py-3">Sistem</th>
                                            <th class="px-4 py-3">Klaim</th>
                                            <th class="px-4 py-3">Variance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($invoice->lines as $line)
                                            <tr>
                                                <td class="px-4 py-3">{{ str_replace('_', ' ', $line->source_type) }}</td>
                                                <td class="px-4 py-3">
                                                    <div class="font-semibold text-gray-900">{{ $line->description }}</div>
                                                    <div class="text-xs text-gray-500">{{ $line->worker?->full_name ?: '-' }}</div>
                                                </td>
                                                <td class="px-4 py-3">{{ number_format((float) $line->quantity, 2, ',', '.') }} {{ $line->unit_name }}</td>
                                                <td class="px-4 py-3">Rp {{ number_format((float) $line->system_amount, 0, ',', '.') }}</td>
                                                <td class="px-4 py-3">Rp {{ number_format((float) $line->claimed_amount, 0, ',', '.') }}</td>
                                                <td class="px-4 py-3">Rp {{ number_format((float) $line->variance_amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada line rekonsiliasi.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <form action="{{ route('external-workforce.vendor-invoices.lines.store', $invoice) }}" method="POST" class="rounded-xl border border-gray-200 p-4">
                                @csrf
                                <h4 class="text-sm font-semibold text-gray-900">Tambah Line</h4>
                                <div class="mt-3 grid gap-3">
                                    <select name="source_type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                        <option value="attendance">Mandays attendance</option>
                                        <option value="piece_output">Output borongan</option>
                                        <option value="manual_adjustment">Manual adjustment</option>
                                    </select>
                                    <select name="source_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                        <option value="">Pilih source atau kosongkan untuk manual</option>
                                        <optgroup label="Mandays">
                                            @foreach($attendanceSources as $source)
                                                <option value="{{ $source->id }}">{{ $source->attendance_number }} - {{ $source->worker?->full_name }} - Rp {{ number_format((float) $source->gross_amount, 0, ',', '.') }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Output Borongan">
                                            @foreach($pieceworkSources as $source)
                                                <option value="{{ $source->id }}">{{ $source->output_number }} - {{ $source->rate?->work_name }} - Rp {{ number_format((float) $source->gross_amount, 0, ',', '.') }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <input name="description" placeholder="Deskripsi optional / manual adjustment" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <div class="grid grid-cols-3 gap-2">
                                        <input type="number" name="quantity" value="1" min="0" step="0.01" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                        <input name="unit_name" value="unit" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                        <input type="number" name="system_rate" value="0" min="0" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    </div>
                                    <input type="number" name="claimed_amount" required min="0" placeholder="Nilai klaim vendor" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <textarea name="notes" rows="2" placeholder="Catatan line" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                                    <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                                        <i class="fas fa-plus"></i> Tambah Line
                                    </button>
                                </div>
                            </form>

                            <form action="{{ route('external-workforce.vendor-invoices.review', $invoice) }}" method="POST" class="rounded-xl border border-gray-200 p-4">
                                @csrf
                                <h4 class="text-sm font-semibold text-gray-900">Review Invoice</h4>
                                <div class="mt-3 grid gap-3">
                                    <select name="decision" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                        <option value="approved">Approved - ready payment</option>
                                        <option value="needs_revision">Need revision</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                    <input name="reviewed_by" value="{{ auth()->user()->name }}" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <textarea name="notes" rows="2" placeholder="Catatan review finance/HR" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                                    <button class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-check-double"></i> Simpan Review
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-gray-500">Belum ada invoice vendor untuk filter ini.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
