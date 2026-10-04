@extends('layouts.sidebar')

@section('title', 'Pencairan Gaji')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Pencairan Gaji</h1>
            <p class="mt-1 text-sm text-gray-500">Siapkan file transfer atau payload host-to-host bank dari slip gaji yang sudah published.</p>
        </div>
        <a href="{{ route('payroll.run-wizard') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-list-check mr-2"></i> Proses Payroll
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Batch</div>
                <div class="mt-1 text-xl font-semibold text-blue-600">{{ $summary['batches'] }}</div>
                <div class="text-xs text-gray-400">Batch pencairan</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Menunggu</div>
                <div class="mt-1 text-xl font-semibold text-amber-600">{{ $summary['pending'] }}</div>
                <div class="text-xs text-gray-400">Review/release</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Approved</div>
                <div class="mt-1 text-xl font-semibold text-green-600">{{ $summary['approved'] }}</div>
                <div class="text-xs text-gray-400">Siap dibayar</div>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Transfer</div>
                <div class="mt-1 text-xl font-semibold text-gray-900">Rp {{ number_format((float) $summary['total'], 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400">Nilai ready</div>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Buat Batch Pencairan Bank</h2>
                <p class="text-sm text-gray-500">Pilih periode payroll, channel bank, dan format output yang akan dipakai finance.</p>
            </div>
            <div class="group relative">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-80 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    Gunakan CSV/TXT untuk upload ke internet banking. Gunakan H2H JSON jika sudah ada koneksi API bank. Item tanpa rekening utama akan diblokir dan tidak masuk total transfer sampai data bank dilengkapi.
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('payroll.disbursements.store') }}" class="mt-5 space-y-4">
            @csrf
            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Data Batch</h3>
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
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Payroll Run</span>
                        <select name="payroll_run_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Opsional</option>
                            @foreach($payrollRuns as $run)
                                <option value="{{ $run->id }}">{{ $run->run_number }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Periode</span>
                        <input name="period" type="month" required value="{{ now()->format('Y-m') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Bayar</span>
                        <input name="scheduled_payment_date" type="date" required value="{{ now()->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Channel Bank</span>
                        <select name="bank_channel" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach($bankChannels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Format Output</span>
                        <select name="file_format" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach($fileFormats as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Rekening Sumber</span>
                        <input name="source_account" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Nomor rekening perusahaan / virtual account">
                    </label>
                    <label class="block md:col-span-4">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</span>
                        <input name="notes" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Instruksi finance, payroll batch, atau catatan release.">
                    </label>
                </div>
            </div>
            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    <i class="fas fa-file-export"></i> Generate Batch Bank
                </button>
            </div>
        </form>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 lg:grid-cols-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Alur Release</h2>
                <p class="mt-1 text-sm text-gray-500">Batch tidak langsung dikirim ke bank. Payroll membuat batch, finance approve, lalu file/payload dipakai untuk release.</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Validasi Rekening</div>
                <p class="mt-2 text-sm text-gray-600">Karyawan tanpa rekening utama otomatis blocked, tidak masuk total transfer, dan perlu diperbaiki di data bank employee.</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Kontrol Integritas</div>
                <p class="mt-2 text-sm text-gray-600">Setiap batch punya checksum SHA-256 untuk memastikan file/payload tidak berubah sebelum release ke bank.</p>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Batch Transfer</h2>
            <p class="text-sm text-gray-500">Review item transfer, approval payroll/finance, dan download file atau payload bank.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Batch</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Transfer</th>
                        <th class="px-5 py-3">Approval</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($batches as $batch)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $batch->batch_number }}</div>
                                <div class="text-xs text-gray-500">{{ $batch->company?->code ?? 'ALL' }} - {{ $bankChannels[$batch->bank_channel] ?? $batch->bank_channel }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $batch->period }}</div>
                                <div class="text-xs text-gray-500">{{ $batch->scheduled_payment_date?->format('d M Y') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">Rp {{ number_format((float) $batch->total_amount, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">{{ $batch->item_count }} item - {{ $batch->items->where('status', 'blocked')->count() }} blocked</div>
                            </td>
                            <td class="px-5 py-4">
                                @foreach($batch->approvals as $approval)
                                    <div class="text-xs {{ $approval->decision === 'approved' ? 'text-green-600' : ($approval->decision === 'rejected' ? 'text-red-600' : 'text-gray-500') }}">{{ $approval->approval_step }}: {{ $approval->decision }}</div>
                                @endforeach
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</span>
                                <div class="mt-1 text-xs text-gray-400">{{ $fileFormats[$batch->file_format] ?? $batch->file_format }}</div>
                                <div class="mt-1 font-mono text-[11px] text-gray-400">{{ $batch->file_checksum ? substr($batch->file_checksum, 0, 12) : '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('payroll.disbursements.download', $batch) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"><i class="fas fa-download mr-1"></i>Export</a>
                                    <form method="POST" action="{{ route('payroll.disbursements.approve', $batch) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="approved">
                                        <button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700"><i class="fas fa-check mr-1"></i>Approve</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada batch transfer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
