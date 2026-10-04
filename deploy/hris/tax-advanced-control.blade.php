@extends('layouts.sidebar')

@section('title', 'Kontrol Lanjutan PPh 21')

@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Kontrol Lanjutan PPh 21</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau koreksi, true-up tahunan, penghasilan tidak teratur, non permanen, simulasi, regression, dan dokumen pajak.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('payroll.tax-foundation') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fas fa-shield-halved mr-2"></i> Fondasi
            </a>
            <a href="{{ route('payroll.tax-corrections') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-rotate mr-2"></i> Koreksi Pajak
            </a>
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Final Bulanan', 'value' => $summary['monthly_final'], 'color' => 'text-green-600', 'sub' => 'Kalkulasi final'],
                ['label' => 'Total PPh 21', 'value' => $money($summary['total_pph21']), 'color' => 'text-red-600', 'sub' => 'Akumulasi bulanan'],
                ['label' => 'True-up', 'value' => $money($summary['true_up_amount']), 'color' => 'text-amber-600', 'sub' => 'Selisih tahunan'],
                ['label' => 'Bukti Potong', 'value' => $summary['withholding_documents'], 'color' => 'text-blue-600', 'sub' => $summary['unpublished_documents'].' belum publish'],
                ['label' => 'Issue Terbuka', 'value' => $summary['open_corrections'] + $summary['annual_draft'] + $summary['failed_regression'], 'color' => 'text-amber-600', 'sub' => 'Perlu follow-up'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Control Board</h2>
                <p class="text-sm text-gray-500">Klik detail untuk masuk ke modul operasional masing-masing.</p>
            </div>
            <div class="group relative">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    Halaman ini dipakai setelah fondasi PPh 21 siap. Tujuannya memastikan kasus lanjutan seperti koreksi, true-up tahunan, income tidak teratur, pekerja non permanen, simulasi, dan regression formula tidak terlewat sebelum closing payroll/pajak.
                </div>
            </div>
        </div>
        <div class="grid gap-4 p-5 lg:grid-cols-3">
            @foreach($controls as $control)
                <a href="{{ route($control['route']) }}" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $control['label'] }}</div>
                            <div class="mt-1 text-sm leading-5 text-gray-500">{{ $control['note'] }}</div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $control['status'] === 'Terkontrol' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ $control['status'] }}</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-gray-100 pt-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900">{{ $control['value'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Alert</div>
                            <div class="mt-1 text-lg font-semibold {{ $control['alert'] > 0 ? 'text-amber-600' : 'text-green-600' }}">{{ $control['alert'] }}</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Koreksi Pajak Terbaru</h2>
                <p class="text-sm text-gray-500">Perubahan angka payroll/tax base setelah perhitungan awal.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentCorrections as $correction)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $correction->correction_number }}</div>
                            <div class="text-sm text-gray-500">{{ $correction->taxProfile?->employee_name ?? '-' }} · {{ $correction->period }} · {{ ucfirst($correction->correction_type) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold {{ (float) $correction->delta_pph21_amount >= 0 ? 'text-red-600' : 'text-green-600' }}">{{ $money($correction->delta_pph21_amount) }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst($correction->status) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada koreksi pajak.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">True-up Tahunan</h2>
                <p class="text-sm text-gray-500">Selisih PPh 21 tahunan dibanding pembayaran bulanan.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentAnnual as $annual)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $annual->taxProfile?->employee_name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $annual->tax_year }} · {{ ucfirst($annual->true_up_status) }} · {{ ucfirst($annual->calculation_status) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold {{ (float) $annual->true_up_amount >= 0 ? 'text-red-600' : 'text-green-600' }}">{{ $money($annual->true_up_amount) }}</div>
                            <div class="text-xs text-gray-500">Due {{ $money($annual->annual_pph21_due) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada true-up tahunan.</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Sandbox Skenario</h2>
                <p class="text-sm text-gray-500">Simulasi pajak sebelum disetujui atau ditampilkan ke employee.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentSimulations as $simulation)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $simulation->scenario_name }}</div>
                            <div class="text-sm text-gray-500">{{ $simulation->simulation_number }} · {{ $simulation->taxProfile?->employee_name ?? '-' }} · {{ str_replace('_', ' ', $simulation->tax_method) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-red-600">{{ $money($simulation->final_tax_amount ?: $simulation->pph21_amount) }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst($simulation->scenario_status) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada simulasi pajak.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Regression Formula</h2>
                <p class="text-sm text-gray-500">Test case untuk menjaga hasil PPh 21 tetap konsisten setelah perubahan formula.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentRegression as $case)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $case->case_number }} - {{ $case->case_name }}</div>
                            <div class="text-sm text-gray-500">{{ $case->period }} · {{ $case->case_type }} · schema {{ $case->coretax_schema_version ?: '-' }}</div>
                        </div>
                        <div class="text-right">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $case->run_status === 'passed' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($case->run_status) }}</span>
                            <div class="mt-1 text-xs text-gray-500">Var {{ $money($case->variance_amount) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada regression case pajak.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
