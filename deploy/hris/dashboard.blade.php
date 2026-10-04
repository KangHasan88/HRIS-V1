@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard HRIS</h1>
            <p class="mt-1 text-sm text-gray-500">Ringkasan performa HR dari karyawan, absensi, cuti, payroll, ESS, WBS, dan delivery modul.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="https://31.97.106.123/kanban/boards/6/wbs" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <i class="bi bi-bar-chart-line-fill text-gray-500"></i>
                Monitor WBS
            </a>
            <a href="https://31.97.106.123/kanban/boards/6/gantt" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="bi bi-calendar2-week-fill"></i>
                Review Gantt
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-5 flex flex-col gap-4 rounded-xl border border-blue-100 bg-blue-50/45 px-4 py-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="text-sm font-semibold text-gray-900">Filter Periode Report</div>
                <div class="mt-1 text-xs text-gray-500">Tahun Berjalan: 2026-01-01 sampai {{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}</div>
                <div class="mt-1 text-[11px] text-gray-400">Tanggal bisa diedit hanya saat preset Custom.</div>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Preset Periode</label>
                    <select class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option>Year to Date</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dari</label>
                    <input class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm disabled:bg-gray-100 disabled:text-gray-500" value="2026-01-01" readonly>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sampai</label>
                    <input class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm disabled:bg-gray-100 disabled:text-gray-500" value="{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}" readonly>
                </div>
                <button class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">Terapkan</button>
                <button class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Export CSV</button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_auto_1fr]">
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Operasional HR</h2>
                        <p class="text-sm text-gray-400">Karyawan, absensi, cuti, payroll, ESS, dan modul aktif.</p>
                    </div>
                    <a class="text-sm font-medium text-blue-600 hover:text-blue-700" href="https://31.97.106.123/kanban/boards/6">Review Card</a>
                </div>
                <div class="grid grid-cols-2 gap-5 md:grid-cols-4">
                    @foreach($stats as $stat)
                        @php
                            $isLongValue = strlen((string) $stat['value']) > 7;
                        @endphp
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $stat['label'] }}</div>
                            <div class="mt-1 font-semibold leading-tight {{ $isLongValue ? 'text-xl' : 'text-2xl' }} {{ $loop->first ? 'text-gray-900' : ($loop->index === 1 ? 'text-green-600' : 'text-blue-600') }}">{{ $stat['value'] }}</div>
                            <div class="text-xs text-gray-400">{{ $stat['trend'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="hidden w-px bg-gray-200 xl:block"></div>

            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Delivery Ringkas</h2>
                        <p class="text-sm text-gray-400">Login, admin panel, module switcher, WBS, dan Gantt.</p>
                    </div>
                    <a class="text-sm font-medium text-blue-600 hover:text-blue-700" href="https://31.97.106.123/kanban/boards/6/wbs">Review WBS</a>
                </div>
                <div class="grid grid-cols-2 gap-5 md:grid-cols-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Ready</div>
                        <div class="mt-1 text-2xl font-semibold text-blue-600">5</div>
                        <div class="text-xs text-gray-400">Card awal</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">WBS</div>
                        <div class="mt-1 text-2xl font-semibold text-green-600">168</div>
                        <div class="text-xs text-gray-400">Linked item</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Target</div>
                        <div class="mt-1 text-2xl font-semibold text-amber-600">31</div>
                        <div class="text-xs text-gray-400">Hari delivery</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Database</div>
                        <div class="mt-1 text-xl font-semibold leading-tight text-green-600">MySQL</div>
                        <div class="text-xs text-gray-400">Aktif</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.3fr_.7fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Progress Delivery Awal</h2>
                    <p class="text-sm text-gray-500">Ringkasan pekerjaan login, admin panel, dan integrasi progress.</p>
                </div>
                <span class="text-xs text-gray-400">Updated {{ now()->timezone('Asia/Jakarta')->format('H:i:s') }}</span>
            </div>
            <div class="mt-5">
                <div class="overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                            <tr>
                                <th class="px-4 py-4">Kode</th>
                                <th class="px-4 py-4">Pekerjaan</th>
                                <th class="px-4 py-4">Tanggal</th>
                                <th class="px-4 py-4">Status</th>
                                <th class="px-4 py-4">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($deliveryRows as $row)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ $row['code'] }}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-gray-900">{{ $row['title'] }}</div>
                                        <div class="text-xs text-gray-400">HRIS - fase login/admin</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row['date'] }}</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $row['status'] }}</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="h-2 w-32 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-blue-600" style="width: {{ $row['progress'] }}%"></div>
                                        </div>
                                        <div class="mt-2 text-xs font-semibold text-blue-600">{{ $row['progress'] }}%</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Health Indicator</h2>
                <p class="text-sm text-gray-500">Indikator cepat untuk monitoring project.</p>
            </div>
            <div class="mt-5 space-y-5">
                @foreach(array_slice($modules, 0, 4) as $module)
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <span class="font-semibold text-gray-900">{{ $module['name'] }}</span>
                            <span class="font-semibold {{ $module['enabled'] ? 'text-green-600' : 'text-red-600' }}">{{ $module['progress'] }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $loop->index === 2 ? 'bg-red-500' : 'bg-blue-500' }}" style="width: {{ $module['progress'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
