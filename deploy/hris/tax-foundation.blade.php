@extends('layouts.sidebar')

@section('title', 'Fondasi PPh 21')

@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Fondasi PPh 21</h1>
            <p class="mt-1 text-sm text-gray-500">Kontrol kesiapan profil pajak, tarif TER, mapping komponen, kalkulasi bulanan, bukti potong, dan Coretax.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('payroll.tax-monthly') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fas fa-percent mr-2"></i> Hitung PPh 21
            </a>
            <a href="{{ route('payroll.coretax-connector') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-plug mr-2"></i> Coretax
            </a>
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Readiness', 'value' => $summary['readiness'].'%', 'color' => $summary['readiness'] >= 80 ? 'text-green-600' : 'text-amber-600', 'sub' => 'Kesiapan fondasi'],
                ['label' => 'Profil Aktif', 'value' => $summary['active_tax_profiles'], 'color' => 'text-blue-600', 'sub' => 'Master PPh 21'],
                ['label' => 'Terhubung', 'value' => $summary['linked_profiles'], 'color' => 'text-green-600', 'sub' => 'Employee profile'],
                ['label' => 'NPWP Verified', 'value' => $summary['verified_npwp'], 'color' => 'text-gray-900', 'sub' => $summary['without_npwp'].' tanpa NPWP'],
                ['label' => 'Tarif TER', 'value' => $summary['ter_rates'], 'color' => 'text-blue-600', 'sub' => 'Rate aktif'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Checklist Kesiapan</h2>
                    <p class="text-sm text-gray-500">Item “Perlu cek” sebaiknya dibereskan sebelum payroll final dan pelaporan pajak.</p>
                </div>
                <div class="group relative">
                    <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                    <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                        Fondasi PPh 21 memastikan data pajak siap dipakai payroll: karyawan link ke profil pajak, NPWP/PTKP valid, tarif TER aktif, komponen payroll sudah dipetakan, dan kanal Coretax disiapkan untuk tahap integrasi.
                    </div>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($checks as $check)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $check['label'] }}</div>
                            <div class="mt-1 text-sm text-gray-500">{{ $check['note'] }}</div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">{{ $check['value'] }} / {{ $check['target'] }}</div>
                        <div class="flex items-center gap-2 md:justify-end">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $check['status'] === 'Siap' ? 'bg-green-50 text-green-700' : ($check['status'] === 'Belum wajib' ? 'bg-gray-100 text-gray-700' : 'bg-amber-50 text-amber-700') }}">{{ $check['status'] }}</span>
                            <a href="{{ route($check['route']) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Detail</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Workflow Pajak</h2>
                <p class="text-sm text-gray-500">Urutan kerja yang disarankan untuk payroll dan compliance.</p>
            </div>
            <div class="mt-4 space-y-3">
                @foreach([
                    ['step' => '1', 'title' => 'Lengkapi master PPh 21', 'route' => 'payroll.tax-profiles', 'desc' => 'NIK, NPWP, PTKP, metode pajak, KPP, dan status aktif.'],
                    ['step' => '2', 'title' => 'Siapkan TER & mapping komponen', 'route' => 'payroll.tax-monthly', 'desc' => 'Tarif kategori A/B/C dan perlakuan pajak komponen payroll.'],
                    ['step' => '3', 'title' => 'Hitung bulanan', 'route' => 'payroll.tax-monthly', 'desc' => 'Draft/final PPh 21 bulanan dari payroll run.'],
                    ['step' => '4', 'title' => 'Review riwayat perhitungan', 'route' => 'payroll.tax-audits', 'desc' => 'Audit trail formula, angka, dan sumber data.'],
                    ['step' => '5', 'title' => 'Generate bukti potong', 'route' => 'payroll.tax-withholding-documents', 'desc' => 'Dokumen ESS dan arsip compliance karyawan.'],
                    ['step' => '6', 'title' => 'Export/Coretax', 'route' => 'payroll.coretax-connector', 'desc' => 'Queue template dan monitor status integrasi.'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="block rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                        <div class="flex gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">{{ $item['step'] }}</span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ $item['title'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-gray-500">{{ $item['desc'] }}</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Distribusi PTKP</h2>
                <p class="text-sm text-gray-500">Komposisi status pajak karyawan aktif.</p>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($ptkpDistribution as $row)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-900">{{ $row->ptkp_status ?: 'Belum diisi' }}</span>
                            <span class="text-gray-500">{{ $row->total }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full bg-blue-600" style="width: {{ $summary['active_tax_profiles'] > 0 ? min(100, ($row->total / $summary['active_tax_profiles']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Belum ada profil pajak aktif.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Aktivitas Hitung</h2>
                <p class="text-sm text-gray-500">Kalkulasi PPh 21 bulanan terbaru.</p>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($recentCalculations as $calculation)
                    <div class="rounded-xl border border-gray-200 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $calculation->taxProfile?->employee_name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $calculation->period }} · {{ strtoupper($calculation->ter_category) }} · {{ ucfirst($calculation->calculation_status) }}</div>
                            </div>
                            <div class="text-right text-sm font-semibold text-red-600">{{ $money($calculation->pph21_amount) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Belum ada kalkulasi PPh 21.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Dokumen Pajak</h2>
                <p class="text-sm text-gray-500">Bukti potong dan dokumen pajak terbaru.</p>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($recentDocuments as $document)
                    <div class="rounded-xl border border-gray-200 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $document->document_number }}</div>
                                <div class="text-xs text-gray-500">{{ $document->taxProfile?->employee_name ?? '-' }} · {{ $document->period }}</div>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($document->status) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Belum ada bukti potong.</div>
                @endforelse
                <div class="grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 text-sm">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Bukti Potong</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">{{ $summary['withholding_documents'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Riwayat</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">{{ $summary['audit_logs'] }}</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
