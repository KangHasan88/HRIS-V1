@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Detektor Anomali Absensi</h1>
            <p class="mt-1 text-sm text-gray-500">Deteksi fraud risk, telat berulang, dan ghost punch dari data clock mobile.</p>
        </div>
        <a href="{{ route('manager.attendance-exceptions') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-map-location-dot"></i> Review Pengecualian
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
                ['label' => 'Total Anomali', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Open', 'value' => $summary['open'], 'color' => 'text-amber-600'],
                ['label' => 'High/Critical', 'value' => $summary['high'], 'color' => 'text-red-600'],
                ['label' => 'Ghost Punch', 'value' => $summary['ghost'], 'color' => 'text-indigo-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Jalankan Scan</h2>
                @include('partials.form-help', [
                    'title' => 'Cara kerja detektor anomali',
                    'description' => 'Scan membaca data clock mobile dalam periode tertentu lalu membuat daftar anomali untuk review HR/atasan.',
                    'steps' => [
                        'Pilih periode scan dan batas jam telat.',
                        'Set minimal telat berulang untuk membuat anomali repeated late.',
                        'Sistem membaca risk flags seperti selfie kosong, liveness rendah, device tidak dipercaya, GPS tidak akurat, dan clock-in telat.',
                        'Hasil scan masuk ke daftar anomali untuk follow-up sebelum payroll atau coaching.',
                    ],
                    'notes' => [
                        'Scan bersifat idempotent: event yang sama tidak akan membuat duplikasi detection.',
                        'Ghost punch diberi severity critical karena berpotensi fraud attendance.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Gunakan scan harian sebelum payroll cutoff atau review compliance.</p>
        </div>

        <form action="{{ route('analytics.attendance-anomalies.scan') }}" method="POST" class="mt-5 grid gap-3 lg:grid-cols-[1fr_1fr_1fr_1fr_auto]">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Periode</label>
                <input type="date" name="period_start" value="{{ now()->subDays(7)->toDateString() }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Periode</label>
                <input type="date" name="period_end" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Batas Jam Telat</label>
                <input type="time" name="late_after_time" value="08:15" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Minimal Telat Berulang</label>
                <input type="number" name="min_repeated_late" value="3" min="2" max="30" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div class="flex items-end">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-magnifying-glass-chart"></i> Scan
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Anomali</h2>
            <p class="text-sm text-gray-500">Insight hasil scan untuk follow-up HR, atasan, payroll, dan compliance.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Anomali</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Evidence</th>
                        <th class="px-5 py-3">Score</th>
                        <th class="px-5 py-3">Rekomendasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($detections as $detection)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $detection->detection_number }}</div>
                                <div class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $detection->anomaly_type)) }} - {{ $detection->detected_at?->format('d M Y H:i') }}</div>
                                <span class="mt-2 inline-flex rounded-full {{ in_array($detection->severity, ['high', 'critical'], true) ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700' }} px-2 py-1 text-xs font-semibold">{{ ucfirst($detection->severity) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $detection->employeeProfile?->full_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $detection->employeeProfile?->employee_number }}</div>
                                <div class="text-xs text-gray-500">{{ $detection->employeeProfile?->branch?->code ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs text-gray-700">{{ $detection->clockEvent?->event_number ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $detection->evidence_snapshot['reason'] ?? '-' }}</div>
                                @if(!empty($detection->evidence_snapshot['risk_flags']))
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($detection->evidence_snapshot['risk_flags'] as $flag)
                                            <span class="rounded-full bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-700">{{ str_replace('_', ' ', $flag) }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ number_format((float) $detection->score, 1) }}</div>
                                <div class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $detection->status)) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="max-w-md text-sm text-gray-600">{{ $detection->recommendation }}</div>
                                <div class="mt-2 text-xs text-gray-500">Periode: {{ $detection->period_start?->format('d M') }} - {{ $detection->period_end?->format('d M Y') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada hasil deteksi anomali.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
