@extends('layouts.sidebar')

@section('content')
@php
    $statusMap = [
        'ready' => ['label' => 'Ready', 'class' => 'bg-green-50 text-green-700', 'bar' => 'bg-green-500', 'icon' => 'fas fa-check'],
        'in_progress' => ['label' => 'Diproses', 'class' => 'bg-blue-50 text-blue-700', 'bar' => 'bg-blue-500', 'icon' => 'fas fa-clock'],
        'attention' => ['label' => 'Perlu Data', 'class' => 'bg-amber-50 text-amber-700', 'bar' => 'bg-amber-500', 'icon' => 'fas fa-circle-exclamation'],
    ];
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Fondasi Payroll</h1>
            <p class="mt-1 text-sm text-gray-500">Kontrol kesiapan data payroll sebelum proses gaji, pajak, BPJS, slip, dan pencairan.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('payroll.run-wizard') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                <i class="fas fa-list-check"></i> Proses Payroll
            </a>
            <a href="{{ route('payroll.components') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">
                <i class="fas fa-sliders"></i> Komponen
            </a>
        </div>
    </div>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4 xl:grid-cols-7">
            @foreach([
                ['label' => 'Readiness', 'value' => $summary['score'].'%', 'color' => 'text-blue-600'],
                ['label' => 'Karyawan Aktif', 'value' => $summary['employee_count'], 'color' => 'text-gray-900'],
                ['label' => 'Checklist Ready', 'value' => $summary['ready_count'].'/'.$summary['readiness_total'], 'color' => 'text-green-600'],
                ['label' => 'Klaim Handoff', 'value' => $summary['pending_claims'], 'color' => 'text-amber-600'],
                ['label' => 'Adjustment', 'value' => $summary['pending_adjustments'], 'color' => 'text-red-600'],
                ['label' => 'Slip Published', 'value' => $summary['published_payslips'], 'color' => 'text-indigo-600'],
                ['label' => 'Run Terakhir', 'value' => $summary['latest_run_status'], 'color' => 'text-cyan-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="mt-5 h-2 rounded-full bg-gray-100">
            <div class="h-2 rounded-full bg-blue-600" style="width: {{ $summary['score'] }}%"></div>
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-3">
        <section class="panel rounded-2xl xl:col-span-2">
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Checklist Kesiapan Payroll</h2>
                    <p class="text-sm text-gray-500">Urutan data minimum agar payroll bisa dihitung dan diaudit dengan rapi.</p>
                </div>
                @include('partials.form-help', [
                    'title' => 'Panduan Fondasi Payroll',
                    'description' => 'Halaman ini dipakai payroll/admin untuk mengecek kesiapan master payroll sebelum menjalankan payroll run.',
                    'steps' => [
                        'Pastikan master karyawan aktif sudah lengkap dan punya struktur gaji.',
                        'Lengkapi komponen payroll, salary band, profil BPJS, dan profil PPh 21.',
                        'Cek klaim reimbursement dan adjustment yang belum masuk payroll.',
                        'Jika checklist sudah aman, lanjut ke Proses Payroll untuk validasi dan simulasi run.',
                    ],
                ])
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($readiness as $item)
                    @php($status = $statusMap[$item['status']] ?? $statusMap['in_progress'])
                    @php($percent = min(100, (int) round(($item['value'] / max(1, $item['target'])) * 100)))
                    <div class="flex flex-col gap-4 px-5 py-4 md:flex-row md:items-center md:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-gray-900">{{ $item['label'] }}</h3>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold {{ $status['class'] }}">
                                    <i class="{{ $status['icon'] }}"></i> {{ $status['label'] }}
                                </span>
                            </div>
                            <div class="mt-2 flex items-center gap-3">
                                <div class="h-2 flex-1 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full {{ $status['bar'] }}" style="width: {{ $percent }}%"></div>
                                </div>
                                <div class="w-20 text-right text-sm font-semibold text-gray-600">{{ $item['value'] }}/{{ $item['target'] }}</div>
                            </div>
                        </div>
                        <a href="{{ $item['route'] }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">{{ $item['action'] }}</a>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Workflow Payroll</h2>
                <p class="text-sm text-gray-500">Pintasan eksekusi utama payroll.</p>
            </div>
            <div class="space-y-3 p-5">
                @foreach([
                    ['label' => '1. Setup komponen', 'route' => route('payroll.components'), 'desc' => 'Earning, deduction, taxable, BPJS basis.'],
                    ['label' => '2. Struktur gaji', 'route' => route('payroll.salary-structures'), 'desc' => 'Salary band dan assignment rahasia.'],
                    ['label' => '3. Proses payroll', 'route' => route('payroll.run-wizard'), 'desc' => 'Cutoff, validasi, anomali, net estimate.'],
                    ['label' => '4. Pencairan gaji', 'route' => route('payroll.disbursements'), 'desc' => 'Bank file, approval, scheduled payment.'],
                    ['label' => '5. Slip gaji', 'route' => route('payroll.payslips'), 'desc' => 'Publish, resend, acknowledgement ESS.'],
                ] as $step)
                    <a href="{{ $step['route'] }}" class="block rounded-xl border border-gray-100 p-4 hover:border-blue-200">
                        <div class="font-semibold text-gray-900">{{ $step['label'] }}</div>
                        <div class="mt-1 text-sm text-gray-500">{{ $step['desc'] }}</div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Payroll Run Terbaru</h2>
                <p class="text-sm text-gray-500">Run dan validasi terakhir sebagai dasar proses gaji.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($latestRuns as $run)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $run->run_number }}</div>
                            <div class="text-xs text-gray-500">{{ $run->period }} - {{ $run->company?->code ?: '-' }} - {{ $run->employee_count }} karyawan</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-blue-600">Rp {{ number_format((float) $run->net_estimate, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $run->status)) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">Belum ada payroll run.</div>
                @endforelse
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Pencairan Terbaru</h2>
                <p class="text-sm text-gray-500">Batch bank file dan status approval pencairan.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($latestDisbursements as $batch)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $batch->batch_number }}</div>
                            <div class="text-xs text-gray-500">{{ $batch->period }} - {{ $batch->bank_channel }} - {{ $batch->item_count }} item</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-green-600">Rp {{ number_format((float) $batch->total_amount, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">Belum ada batch pencairan.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
