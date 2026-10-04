@extends('layouts.sidebar')

@section('content')
@php
    $typeLabels = [
        'all' => 'Semua event',
        'leave' => 'Cuti',
        'shift' => 'Shift',
        'trip' => 'Dinas',
        'overtime' => 'Lembur',
        'cutoff' => 'Cutoff',
    ];
    $visibleEvents = $filters['type'] === 'all' ? $events : $events->where('type', $filters['type'])->values();
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Kalender Tim</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau cuti, shift, dinas, lembur, dan cutoff payroll dalam satu kalender manager.</p>
        </div>
        <a href="{{ route('manager.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-chart-line"></i> Dashboard Manager
        </a>
    </div>

    <section class="panel rounded-2xl p-5">
        <form method="GET" class="grid gap-3 lg:grid-cols-[1fr_1fr_1fr_auto]">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Periode</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Periode</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Event</label>
                <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
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
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Tim Aktif', 'value' => $summary['team_total'], 'color' => 'text-gray-900'],
                ['label' => 'Event Cuti', 'value' => $summary['leave'], 'color' => 'text-blue-600'],
                ['label' => 'Event Shift', 'value' => $summary['shift'], 'color' => 'text-green-600'],
                ['label' => 'Event Dinas', 'value' => $summary['trip'], 'color' => 'text-purple-600'],
                ['label' => 'Cutoff', 'value' => $summary['cutoff'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[1.4fr_.8fr]">
        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Timeline Event</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara membaca Kalender Tim',
                        'description' => 'Kalender ini membantu manager melihat benturan jadwal tim sebelum menyetujui cuti, dinas, lembur, atau perubahan shift.',
                        'steps' => [
                            'Pilih periode yang mau dicek, misalnya bulan berjalan.',
                            'Filter tipe event jika hanya ingin melihat cuti, shift, dinas, lembur, atau cutoff payroll.',
                            'Gunakan marker cutoff sebagai reminder untuk menyelesaikan approval sebelum payroll diproses.',
                        ],
                        'notes' => [
                            'Cutoff payroll saat ini memakai marker default tanggal 25 dan akhir bulan.',
                            'Jika ada master payroll cutoff khusus, kalender bisa diarahkan ke konfigurasi tersebut.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Event diurutkan berdasarkan tanggal mulai dan tetap menampilkan range jika lebih dari satu hari.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($visibleEvents as $event)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[140px_1fr_auto]">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $event['date']->format('d M Y') }}</div>
                            @if($event['end_date'] && $event['end_date']->toDateString() !== $event['date']->toDateString())
                                <div class="text-xs text-gray-500">s/d {{ $event['end_date']->format('d M Y') }}</div>
                            @endif
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $event['label'] }}</div>
                            <div class="text-sm text-gray-500">{{ $event['employee'] }} - {{ $event['meta'] }}</div>
                            <div class="mt-1 font-mono text-xs text-gray-400">{{ $event['source'] }}</div>
                        </div>
                        <div class="md:text-right">
                            <span class="inline-flex rounded-full {{ $event['color'] }} px-2 py-1 text-xs font-semibold">{{ ucfirst($event['type']) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada event pada filter periode ini.</div>
                @endforelse
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Anggota Scope</h2>
                <p class="text-sm text-gray-500">Karyawan yang masuk scope kalender manager.</p>
            </div>
            <div class="max-h-[620px] divide-y divide-gray-100 overflow-y-auto">
                @forelse($team as $employee)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $employee->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $employee->employee_number }} - {{ $employee->position ?: 'No position' }}</div>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <div>{{ $employee->branch?->code ?: '-' }}</div>
                            <div>{{ $employee->department ?: '-' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada anggota tim.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
