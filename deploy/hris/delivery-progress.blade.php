@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Progress Delivery</h1>
            <p class="mt-1 text-sm text-gray-500">Ringkasan live dari Kanban, WBS, dan status delivery HRIS.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="https://31.97.106.123/kanban/boards/6" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <i class="fas fa-table-columns"></i> Buka Kanban
            </a>
            <a href="https://31.97.106.123/kanban/boards/6/gantt" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                <i class="fas fa-chart-gantt"></i> Buka Gantt
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Total Card', 'value' => $summary['cards'], 'color' => 'text-gray-900'],
                ['label' => 'WBS Item', 'value' => $summary['wbs'], 'color' => 'text-blue-600'],
                ['label' => 'Linked WBS', 'value' => $summary['linked'], 'color' => 'text-green-600'],
                ['label' => 'Belum Link', 'value' => $summary['notLinked'], 'color' => 'text-yellow-600'],
                ['label' => 'Overdue', 'value' => $summary['overdue'], 'color' => 'text-red-600'],
                ['label' => 'Done', 'value' => $summary['done'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[.9fr_1.3fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Status Kanban</h2>
                <p class="text-sm text-gray-500">Jumlah card per list delivery.</p>
            </div>
            <div class="mt-5 space-y-4">
                @foreach($listSummary as $list)
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-900">{{ $list['name'] }}</span>
                            <span class="font-semibold text-blue-600">{{ $list['count'] }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full bg-blue-500" style="width: {{ min($list['count'], 100) }}%"></div>
                        </div>
                        @if($list['high'] > 0)
                            <div class="mt-1 text-xs text-red-500">{{ $list['high'] }} high priority</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Card Terdekat</h2>
                    <p class="text-sm text-gray-500">Urutan pekerjaan berdasarkan due date.</p>
                </div>
                <a href="https://31.97.106.123/kanban/boards/6/wbs" class="text-sm font-medium text-blue-600 hover:text-blue-700">Review WBS</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Card</th>
                            <th class="px-5 py-3">WBS</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($latestCards as $card)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $card['title'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $card['priority'] }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $card['wbs'] ?? '-' }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $card['list'] }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $card['due_date'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
