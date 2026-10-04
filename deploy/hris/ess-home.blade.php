@extends('layouts.sidebar')

@section('content')
<div class="space-y-6 ess-webview-page">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Beranda ESS</h1>
            <p class="mt-1 text-sm text-gray-500">Ringkasan jadwal hari ini, pengajuan yang perlu dipantau, dan shortcut layanan karyawan.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('ess.request-center.entry') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-paper-plane"></i>
                Buka ESS
            </a>
            <a href="{{ route('ess.documents') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <i class="fas fa-folder-open"></i>
                Dokumen
            </a>
        </div>
    </div>

    @if(! $employee)
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-sm text-yellow-800">
            Akun login belum terhubung ke profil karyawan. Hubungi HR/admin agar jadwal, pengajuan, dan slip gaji personal bisa muncul.
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[1.1fr_.9fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm mobile-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $today->translatedFormat('l, d M Y') }}</div>
                    <h2 class="mt-1 text-lg font-semibold text-gray-900">Jadwal Hari Ini</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $employee?->full_name ?? 'Profil karyawan belum tersedia' }}</p>
                </div>
                <span class="rounded-full {{ $isOffDay ? 'bg-yellow-50 text-yellow-600' : 'bg-green-50 text-green-600' }} px-3 py-1 text-xs font-semibold">
                    {{ $isOffDay ? 'Off Day' : 'Aktif' }}
                </span>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Shift</div>
                    <div class="mt-1 text-base font-semibold text-gray-900">{{ $shift?->shiftPattern?->name ?? 'Belum ada roster' }}</div>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Jam Kerja</div>
                    <div class="mt-1 text-base font-semibold text-gray-900">
                        @if($shift?->shiftPattern)
                            {{ substr($shift->shiftPattern->start_time, 0, 5) }} - {{ substr($shift->shiftPattern->end_time, 0, 5) }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Lokasi</div>
                    <div class="mt-1 text-base font-semibold text-gray-900">{{ $employee?->branch?->code ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm mobile-card">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Pengajuan Pending</h2>
                    <p class="text-sm text-gray-500">Yang perlu dipantau dari sisi karyawan.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ $pendingActions->sum('value') }} item</span>
            </div>
            <div class="mt-4 space-y-3">
                @foreach($pendingActions as $action)
                    <a href="{{ $action['route'] }}" class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 transition hover:bg-blue-50">
                        <span class="text-sm font-medium text-gray-700">{{ $action['label'] }}</span>
                        <span class="text-lg font-semibold {{ $action['value'] > 0 ? 'text-blue-600' : 'text-gray-400' }}">{{ $action['value'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['label' => 'Pusat Pengajuan', 'desc' => 'Ajukan cuti, lembur, dinas, dan koreksi absensi.', 'icon' => 'fas fa-paper-plane', 'route' => route('ess.request-center')],
            ['label' => 'Update Profil', 'desc' => 'Ubah kontak, alamat, kontak darurat, dan rekening.', 'icon' => 'fas fa-user-pen', 'route' => route('ess.profile-updates')],
            ['label' => 'Klaim Reimbursement', 'desc' => 'Klaim biaya kerja dengan bukti struk kamera.', 'icon' => 'fas fa-receipt', 'route' => route('ess.reimbursements')],
            ['label' => 'Helpdesk HR', 'desc' => 'Buat tiket bantuan HR dan pantau SLA.', 'icon' => 'fas fa-headset', 'route' => route('ess.helpdesk')],
            ['label' => 'Slip Gaji', 'desc' => $lastPayslip ? 'Periode terbaru ' . $lastPayslip->period : 'Menunggu publish payroll.', 'icon' => 'fas fa-file-invoice', 'route' => route('ess.payslips')],
        ] as $shortcut)
            <a href="{{ $shortcut['route'] }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md mobile-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <i class="{{ $shortcut['icon'] }}"></i>
                </div>
                <h2 class="mt-4 text-base font-semibold text-gray-900">{{ $shortcut['label'] }}</h2>
                <p class="mt-1 text-sm leading-5 text-gray-500">{{ $shortcut['desc'] }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
