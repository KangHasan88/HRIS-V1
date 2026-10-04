@extends('layouts.sidebar')

@section('content')
@php
    $statusMap = [
        'done' => ['label' => 'Selesai', 'class' => 'bg-green-50 text-green-700', 'bar' => 'bg-green-500', 'icon' => 'fas fa-check'],
        'in_progress' => ['label' => 'Berjalan', 'class' => 'bg-blue-50 text-blue-700', 'bar' => 'bg-blue-500', 'icon' => 'fas fa-clock'],
        'attention' => ['label' => 'Perlu Aksi', 'class' => 'bg-amber-50 text-amber-700', 'bar' => 'bg-amber-500', 'icon' => 'fas fa-circle-exclamation'],
    ];
@endphp
<div class="ess-webview-page space-y-5 md:space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 md:text-2xl">Alur ESS Saya</h1>
            <p class="mt-1 text-sm text-gray-500">Ringkasan perjalanan layanan karyawan dari profil, pengajuan, payroll, sampai dokumen.</p>
        </div>
        <a href="{{ route('ess.request-center') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm">
            <i class="fas fa-paper-plane"></i> Buat Pengajuan
        </a>
    </div>

    @unless ($employee)
        <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <div class="font-semibold">Akun login belum terhubung ke profil karyawan.</div>
            <div class="mt-1">Alur ESS akan aktif setelah HR/admin menghubungkan user ini ke Employee Profile.</div>
        </div>
    @endunless

    <div class="mobile-card rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:p-5">
        <div class="grid gap-4 md:grid-cols-[1.2fr_2fr] md:items-center">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Progress ESS</div>
                <div class="mt-1 text-2xl font-semibold text-blue-600">{{ $summary['progress'] }}%</div>
                <div class="mt-2 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-blue-600" style="width: {{ $summary['progress'] }}%"></div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Selesai</div>
                    <div class="mt-1 text-xl font-semibold text-green-600">{{ $summary['done'] }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Berjalan</div>
                    <div class="mt-1 text-xl font-semibold text-blue-600">{{ $summary['in_progress'] }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Perlu Aksi</div>
                    <div class="mt-1 text-xl font-semibold text-amber-600">{{ $summary['attention'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mobile-card rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Flow Karyawan</h2>
                <p class="text-sm text-gray-500">Setiap item mengarah ke halaman ESS terkait agar karyawan tahu apa yang perlu dicek.</p>
            </div>
            @include('partials.form-help', [
                'title' => 'Panduan Alur ESS',
                'description' => 'Halaman ini menjadi kompas karyawan untuk melihat layanan ESS yang sudah lengkap, sedang diproses, atau perlu aksi.',
                'steps' => [
                    'Cek status setiap kelompok alur: Profil, Pengajuan, Klaim & Payroll, serta Dokumen & Bantuan.',
                    'Klik tombol aksi pada item untuk membuka halaman ESS terkait.',
                    'Status Perlu Aksi berarti ada data yang perlu dikonfirmasi karyawan, misalnya slip gaji belum di-acknowledge.',
                    'Status Berjalan berarti ada request yang sedang menunggu approval atau proses HR/payroll.',
                ],
            ])
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($flows->groupBy('group') as $group => $items)
                <section class="p-5">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $group }}</h3>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                        @foreach($items as $flow)
                            @php($status = $statusMap[$flow['status']] ?? $statusMap['in_progress'])
                            <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-4">
                                <div class="absolute inset-y-0 left-0 w-1 {{ $status['bar'] }}"></div>
                                <div class="flex items-start justify-between gap-3 pl-2">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-sm font-semibold text-gray-900">{{ $flow['title'] }}</h4>
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold {{ $status['class'] }}">
                                                <i class="{{ $status['icon'] }}"></i> {{ $status['label'] }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm leading-5 text-gray-500">{{ $flow['description'] }}</p>
                                        <div class="mt-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $flow['meta'] }}</div>
                                    </div>
                                    <a href="{{ $flow['route'] }}" class="shrink-0 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        {{ $flow['action'] }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="p-5 text-sm text-gray-500">Alur ESS belum tersedia karena profil karyawan belum terhubung.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
