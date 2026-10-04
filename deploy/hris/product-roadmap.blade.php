@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Product Roadmap</h1>
            <p class="mt-1 text-sm text-gray-500">Strategi rilis, slicing pekerjaan, readiness gate, dan risiko delivery HRIS.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('settings.delivery-progress') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <i class="fas fa-chart-line"></i> Progress Delivery
            </a>
            <a href="https://31.97.106.123/kanban/boards/6/gantt" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                <i class="fas fa-chart-gantt"></i> Buka Gantt
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach($gateSummary as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $item['value'] }}</div>
                    <div class="mt-1 text-xs font-medium text-blue-600">{{ $item['status'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="border-b border-gray-100 pb-4">
            <h2 class="text-lg font-semibold text-gray-900">Slicing Rilis 1 Bulan</h2>
            <p class="text-sm text-gray-500">Fase kerja dari foundation sampai stabilization, memakai status Kanban sebagai gate readiness.</p>
        </div>
        <div class="mt-5 grid gap-4 xl:grid-cols-4">
            @foreach($releaseWindows as $window)
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ \Illuminate\Support\Carbon::parse($window['start'])->format('d M') }} - {{ \Illuminate\Support\Carbon::parse($window['end'])->format('d M Y') }}</div>
                    <div class="mt-2 min-h-12 text-base font-semibold text-gray-900">{{ $window['name'] }}</div>
                    <div class="mt-4 flex items-end justify-between">
                        <div>
                            <div class="text-xs text-gray-500">Card</div>
                            <div class="text-xl font-semibold text-gray-900">{{ $window['cards'] }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Ready Gate</div>
                            <div class="text-xl font-semibold {{ $window['risk'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $window['progress'] }}%</div>
                        </div>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-gray-100">
                        <div class="h-2 rounded-full bg-blue-600" style="width: {{ min($window['progress'], 100) }}%"></div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">{{ $window['testing'] }} card testing/done, {{ $window['risk'] }} risiko aktif.</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_.9fr]">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Roadmap per Modul</h2>
                <p class="text-sm text-gray-500">Distribusi backlog, ready, testing, dan risiko per area produk.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Modul</th>
                            <th class="px-5 py-3">Card</th>
                            <th class="px-5 py-3">Ready</th>
                            <th class="px-5 py-3">Testing</th>
                            <th class="px-5 py-3">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($moduleSummary as $module)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $module['group'] }}</div>
                                    <div class="text-xs {{ $module['risk'] > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ $module['risk'] }} risiko overdue aktif</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $module['cards'] }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $module['ready'] }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $module['testing'] }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-28 rounded-full bg-gray-100">
                                            <div class="h-2 rounded-full bg-blue-600" style="width: {{ min($module['progress'], 100) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-blue-600">{{ $module['progress'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Next Slice</h2>
                <p class="text-sm text-gray-500">Card aktif berikutnya yang perlu diprioritaskan.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($nextCards as $card)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $card['title'] }}</div>
                                <div class="mt-1 text-xs text-gray-500">WBS {{ $card['wbs'] ?: '-' }} / {{ $card['status'] }} / {{ $card['group'] }}</div>
                            </div>
                            <div class="text-right text-xs font-semibold {{ $card['is_overdue'] ? 'text-red-600' : 'text-gray-500' }}">
                                {{ $card['due_date'] ? $card['due_date']->format('d M') : '-' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Semua card sudah masuk Testing/Done.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
