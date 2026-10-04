@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Penyesuaian Payroll</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola retroaktif, backpay, koreksi komponen, dan audit impact payroll.</p>
        </div>
        <a href="{{ route('ess.payroll-adjustments') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-mobile-screen-button"></i> Preview ESS
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <form method="GET" class="grid gap-3 lg:grid-cols-[1fr_1fr_auto]">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode Posting</label>
                <input type="month" name="period" value="{{ $filters['period'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua status</option>
                    @foreach(['draft' => 'Draft', 'submitted' => 'Menunggu', 'approved' => 'Approved', 'posted' => 'Posted', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Total Item', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Posted', 'value' => $summary['posted'], 'color' => 'text-blue-600'],
                ['label' => 'Net Impact', 'value' => 'Rp ' . number_format($summary['net'], 0, ',', '.'), 'color' => $summary['net'] < 0 ? 'text-red-600' : 'text-gray-900'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[.75fr_1.25fr]">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Adjustment</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara kerja penyesuaian payroll',
                        'description' => 'Form ini dipakai ketika payroll periode sebelumnya perlu dikoreksi dan dibayarkan/dipotong pada periode berjalan.',
                        'steps' => [
                            'Pilih karyawan dan periode sumber yang dikoreksi.',
                            'Isi komponen earning/deduction, nominal, dan treatment pajak.',
                            'Review impact gross, deduction, taxable, dan net sebelum posting.',
                        ],
                        'notes' => [
                            'Backpay biasanya earning dan taxable sesuai kebijakan.',
                            'Deduction reversal dapat dibuat sebagai earning jika mengembalikan potongan.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Semua perubahan tercatat di audit trail payroll.</p>
            </div>

            <form action="{{ route('payroll.adjustments.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Karyawan & Periode</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Adjustment</label>
                            <input name="adjustment_number" required placeholder="Contoh: ADJ-202607-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Payroll Run</label>
                            <select name="payroll_run_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Link opsional</option>
                                @foreach($payrollRuns as $run)
                                    <option value="{{ $run->id }}">{{ $run->run_number }} - {{ $run->period }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dari Periode</label>
                            <input type="month" name="period_from" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sampai Periode</label>
                            <input type="month" name="period_to" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode Posting</label>
                            <input type="month" name="posting_period" value="{{ $filters['period'] }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Adjustment</label>
                            <select name="adjustment_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="retroactive_salary">Retroaktif gaji</option>
                                <option value="backpay">Backpay</option>
                                <option value="correction">Koreksi payroll</option>
                                <option value="deduction_reversal">Pembalikan potongan</option>
                                <option value="manual_adjustment">Manual adjustment</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Komponen & Impact</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Komponen</label>
                            <input name="component_code" required placeholder="Contoh: EARN-BACKPAY" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Komponen</label>
                            <input name="component_name" required placeholder="Contoh: Backpay kenaikan gaji" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Arah Komponen</label>
                            <select name="component_direction" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="earning">Menambah take home pay</option>
                                <option value="deduction">Mengurangi take home pay</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Treatment Pajak</label>
                            <select name="tax_treatment" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="regular_taxable">Taxable reguler</option>
                                <option value="irregular_taxable">Taxable tidak teratur</option>
                                <option value="non_taxable">Non-pajak</option>
                                <option value="deduction_adjustment">Adjustment potongan</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nominal Dasar</label>
                            <input type="number" name="base_amount" value="0" min="0" step="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nominal Adjustment</label>
                            <input type="number" name="adjustment_amount" min="1" step="1" required placeholder="Contoh: 1500000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Approval & Audit</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="submitted">Menunggu review</option>
                                <option value="approved">Approved</option>
                                <option value="draft">Draft</option>
                                <option value="posted">Posted</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan</label>
                            <input name="reason" placeholder="Contoh: Kenaikan gaji berlaku mundur" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Audit</label>
                            <textarea name="audit_notes" rows="3" placeholder="Dokumen dasar, approval, atau referensi ticket payroll." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Adjustment
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Adjustment</h2>
                <p class="text-sm text-gray-500">Impact gross, deduction, taxable, dan net tersimpan untuk audit payroll.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Adjustment</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3">Impact</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($adjustments as $adjustment)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $adjustment->adjustment_number }}</div>
                                    <div class="text-xs text-gray-500">{{ str_replace('_', ' ', $adjustment->adjustment_type) }}</div>
                                    <div class="text-xs text-gray-500">{{ $adjustment->component_code }} - {{ $adjustment->component_name }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $adjustment->employeeProfile?->full_name ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $adjustment->employeeProfile?->employee_number ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">{{ $adjustment->period_from }} - {{ $adjustment->period_to }}</div>
                                    <div class="text-xs text-gray-500">Posting {{ $adjustment->posting_period }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold {{ ($adjustment->impact_snapshot['net_impact'] ?? 0) < 0 ? 'text-red-600' : 'text-gray-900' }}">Rp {{ number_format($adjustment->impact_snapshot['net_impact'] ?? 0, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">Taxable Rp {{ number_format((float) $adjustment->taxable_amount, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ ucfirst($adjustment->status) }}</span>
                                    <div class="mt-2 text-xs text-gray-500">{{ $adjustment->approved_by ?: 'Belum approved' }}</div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if(! in_array($adjustment->status, ['posted', 'rejected'], true))
                                        <form method="POST" action="{{ route('payroll.adjustments.approve', $adjustment) }}" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="decision" value="posted">
                                            <input type="hidden" name="audit_notes" value="Posted dari review payroll adjustment.">
                                            <button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">Post</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada penyesuaian payroll untuk filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
