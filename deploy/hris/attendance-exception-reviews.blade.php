@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Review Pengecualian Absensi</h1>
            <p class="mt-1 text-sm text-gray-500">Validasi clock event bermasalah memakai bukti lokasi, foto, device, dan risk flags.</p>
        </div>
        <a href="{{ route('manager.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-chart-line"></i> Dashboard Manager
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Total Exception', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Pending Review', 'value' => $summary['pending'], 'color' => 'text-amber-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Rejected', 'value' => $summary['rejected'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel rounded-2xl">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Antrian Review</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara review pengecualian absensi',
                        'description' => 'Halaman ini dipakai atasan untuk memutuskan clock event yang ditandai sistem karena GPS, foto, device, atau liveness tidak normal.',
                        'steps' => [
                            'Cek nama karyawan, jam clock, lokasi, foto, device, dan risk flags.',
                            'Buka map jika koordinat tersedia, lalu cocokkan dengan lokasi kerja atau izin operasional.',
                            'Pilih approve jika bukti valid, reject jika tidak valid, atau follow-up jika perlu klarifikasi HR.',
                            'Isi catatan review supaya audit payroll dan compliance jelas.',
                        ],
                        'notes' => [
                            'Approve akan mengubah status clock event menjadi approved.',
                            'Reject akan mengubah status clock event menjadi rejected dan tetap tercatat di audit trail.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Fokus ke exception yang punya risk flags atau bukti tidak lengkap.</p>
            </div>
            <form method="GET" action="{{ route('manager.attendance-exceptions') }}" class="flex items-center gap-2">
                <select name="review_status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua status</option>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'follow_up' => 'Follow-up'] as $value => $label)
                        <option value="{{ $value }}" @selected($filterStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Filter</button>
            </form>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($events as $event)
                @php
                    $mapUrl = ($event->latitude && $event->longitude) ? 'https://www.google.com/maps?q=' . $event->latitude . ',' . $event->longitude : null;
                @endphp
                <div class="grid gap-4 px-5 py-5 xl:grid-cols-[1.2fr_.9fr_1fr]">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $event->employeeProfile?->full_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $event->employeeProfile?->employee_number }} - {{ $event->employeeProfile?->branch?->code ?: '-' }}</div>
                            </div>
                            <span class="rounded-full {{ $event->review_status === 'approved' ? 'bg-green-50 text-green-700' : ($event->review_status === 'rejected' ? 'bg-red-50 text-red-700' : ($event->review_status === 'follow_up' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700')) }} px-3 py-1 text-xs font-semibold">{{ ucwords(str_replace('_', ' ', $event->review_status ?: 'pending')) }}</span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-gray-100 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Clock Event</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ $event->event_number }}</div>
                                <div class="text-xs text-gray-500">{{ str_replace('_', ' ', $event->event_type) }} - {{ $event->event_at?->format('d M Y H:i') }}</div>
                            </div>
                            <div class="rounded-xl border border-gray-100 p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Device & Liveness</div>
                                <div class="mt-1 text-sm text-gray-700">{{ $event->device_name ?: ($event->deviceBinding?->device_name ?: 'Device tidak tercatat') }}</div>
                                <div class="text-xs text-gray-500">Liveness {{ $event->liveness_score }} - {{ $event->is_device_trusted ? 'Trusted' : 'Untrusted' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-xl border border-gray-100 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Bukti Lokasi</div>
                            <div class="mt-1 font-mono text-sm text-gray-900">{{ $event->latitude ?: '-' }}, {{ $event->longitude ?: '-' }}</div>
                            <div class="text-xs text-gray-500">Akurasi {{ $event->gps_accuracy_meter ?: '-' }} meter - {{ $event->location_label ?: 'Tanpa label' }}</div>
                            @if($mapUrl)
                                <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-2 text-xs font-semibold text-blue-600">
                                    <i class="fas fa-map-location-dot"></i> Buka Map
                                </a>
                            @else
                                <div class="mt-2 text-xs font-semibold text-red-600">Koordinat belum tersedia</div>
                            @endif
                        </div>
                        <div class="rounded-xl border border-gray-100 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Bukti Foto</div>
                            <div class="mt-1 break-all text-sm text-gray-700">{{ $event->selfie_reference ?: 'Foto selfie belum tersedia' }}</div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-3 flex flex-wrap gap-2">
                            @forelse($event->risk_flags ?? [] as $flag)
                                <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-semibold text-red-700">{{ str_replace('_', ' ', $flag) }}</span>
                            @empty
                                <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-semibold text-gray-500">Tidak ada risk flag</span>
                            @endforelse
                        </div>
                        <form action="{{ route('manager.attendance-exceptions.update', $event) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <select name="review_decision" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih keputusan</option>
                                <option value="approve_exception">Approve exception</option>
                                <option value="reject_exception">Reject exception</option>
                                <option value="needs_hr_follow_up">Butuh follow-up HR</option>
                            </select>
                            <textarea name="review_notes" required rows="3" placeholder="Catatan review: alasan approve/reject/follow-up." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ $event->review_notes }}</textarea>
                            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                <i class="fas fa-check-circle"></i> Simpan Review
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-sm text-gray-500">Belum ada pengecualian absensi untuk difollow-up.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
