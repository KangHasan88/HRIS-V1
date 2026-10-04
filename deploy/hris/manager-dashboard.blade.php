@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard Manager</h1>
            <p class="mt-1 text-sm text-gray-500">Ringkasan absensi tim, approval cuti/lembur, dan ulang tahun karyawan.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('leave.requests') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">
                <i class="fas fa-calendar-check"></i> Review Cuti
            </a>
            <a href="{{ route('attendance.overtime-requests') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                <i class="fas fa-stopwatch"></i> Review Lembur
            </a>
        </div>
    </div>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4 xl:grid-cols-7">
            @foreach([
                ['label' => 'Tim Aktif', 'value' => $summary['team_total'], 'color' => 'text-gray-900'],
                ['label' => 'Hadir Hari Ini', 'value' => $summary['present_today'], 'color' => 'text-green-600'],
                ['label' => 'Belum Clock-in', 'value' => $summary['not_clocked_in'], 'color' => 'text-amber-600'],
                ['label' => 'Need Review', 'value' => $summary['need_review'], 'color' => 'text-red-600'],
                ['label' => 'Cuti Pending', 'value' => $summary['leave_pending'], 'color' => 'text-blue-600'],
                ['label' => 'Lembur Pending', 'value' => $summary['overtime_pending'], 'color' => 'text-indigo-600'],
                ['label' => 'Ultah 30 Hari', 'value' => $summary['birthday_30_days'], 'color' => 'text-pink-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-3">
        <section class="panel rounded-2xl xl:col-span-2">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Approval Tim</h2>
                <p class="text-sm text-gray-500">Cuti dan lembur yang menunggu review manager.</p>
            </div>
            <div class="grid divide-y divide-gray-100 lg:grid-cols-2 lg:divide-x lg:divide-y-0">
                <div class="p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Cuti Pending</h3>
                        <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $leaveRequests->count() }} item</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($leaveRequests->take(6) as $request)
                            <div class="rounded-xl border border-gray-100 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $request->employeeProfile?->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $request->request_number }} - {{ $request->policyType?->name ?: 'Cuti' }}</div>
                                    </div>
                                    <span class="rounded-full bg-yellow-50 px-2 py-1 text-xs font-semibold text-yellow-700">{{ ucfirst($request->approval_status) }}</span>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">{{ optional($request->start_date)->format('d M') }} - {{ optional($request->end_date)->format('d M Y') }} - {{ $request->requested_days }} hari</div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-200 p-5 text-sm text-gray-500">Tidak ada cuti pending.</div>
                        @endforelse
                    </div>
                </div>

                <div class="p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Lembur Pending</h3>
                        <span class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">{{ $overtimeRequests->count() }} item</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($overtimeRequests->take(6) as $request)
                            <div class="rounded-xl border border-gray-100 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $request->employeeProfile?->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $request->request_number }} - {{ str_replace('_', ' ', $request->overtime_type) }}</div>
                                    </div>
                                    <span class="rounded-full bg-yellow-50 px-2 py-1 text-xs font-semibold text-yellow-700">{{ ucfirst($request->approval_status) }}</span>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">{{ optional($request->overtime_date)->format('d M Y') }} - {{ $request->requested_hours }} jam</div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-200 p-5 text-sm text-gray-500">Tidak ada lembur pending.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Ulang Tahun Tim</h2>
                <p class="text-sm text-gray-500">Birthday terdekat berdasarkan data karyawan.</p>
            </div>
            <div class="space-y-3 p-5">
                @forelse($birthdays as $birthday)
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 p-3">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $birthday['employee']->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $birthday['employee']->department ?: 'No department' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-pink-600">{{ $birthday['date']->format('d M') }}</div>
                            <div class="text-xs text-gray-500">{{ $birthday['days'] }} hari</div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-200 p-5 text-sm text-gray-500">Belum ada data ulang tahun.</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Absensi Hari Ini</h2>
                <p class="text-sm text-gray-500">Clock event terbaru dari anggota tim.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($clockEvents as $event)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $event->employeeProfile?->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ str_replace('_', ' ', $event->event_type) }} - {{ optional($event->event_at)->format('H:i') }} - {{ $event->source }}</div>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $event->status === 'approved' ? 'bg-green-50 text-green-700' : ($event->status === 'need_review' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700') }}">{{ ucfirst(str_replace('_', ' ', $event->status)) }}</span>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">Belum ada clock event hari ini.</div>
                @endforelse
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Anggota Tim</h2>
                <p class="text-sm text-gray-500">Daftar ringkas karyawan dalam scope manager.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($team->take(12) as $employee)
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
                    <div class="p-8 text-center text-sm text-gray-500">Belum ada anggota tim dalam scope manager.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
