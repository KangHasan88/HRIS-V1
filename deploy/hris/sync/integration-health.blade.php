@extends('layouts.sidebar')

@section('title', 'Health Integrasi & SLA')

@section('content')
@php
    $scoreColor = $summary['overall_score'] >= 85 ? 'text-green-600' : ($summary['overall_score'] >= 65 ? 'text-amber-600' : 'text-red-600');
    $statusClass = fn ($status) => [
        'healthy' => 'bg-green-50 text-green-700',
        'warning' => 'bg-amber-50 text-amber-700',
        'inactive' => 'bg-gray-100 text-gray-600',
    ][$status] ?? 'bg-gray-100 text-gray-600';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Health Integrasi & SLA</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau queue, failed job, SLA approval, notifikasi, dan connector kritikal HRIS.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('settings.audit-trail') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fas fa-list-check mr-2"></i> Audit Trail
            </a>
            <a href="{{ route('settings.notification-center') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-bell mr-2"></i> Notifikasi
            </a>
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Health Score', 'value' => $summary['overall_score'].'%', 'color' => $scoreColor, 'sub' => 'Gabungan SLA + queue'],
                ['label' => 'Queue Backlog', 'value' => $summary['queue_backlog'], 'color' => $summary['queue_backlog'] > 0 ? 'text-amber-600' : 'text-green-600', 'sub' => 'Pending jobs'],
                ['label' => 'Failed Jobs', 'value' => $summary['failed_jobs'], 'color' => $summary['failed_jobs'] > 0 ? 'text-red-600' : 'text-green-600', 'sub' => '7 hari terakhir'],
                ['label' => 'SLA Breach', 'value' => $summary['sla_breach'], 'color' => $summary['sla_breach'] > 0 ? 'text-red-600' : 'text-green-600', 'sub' => 'Approval lewat batas'],
                ['label' => 'Connector Issue', 'value' => $summary['connector_issue'], 'color' => $summary['connector_issue'] > 0 ? 'text-amber-600' : 'text-green-600', 'sub' => 'Perlu review'],
                ['label' => 'Notif Issue', 'value' => $summary['notification_issue'], 'color' => $summary['notification_issue'] > 0 ? 'text-amber-600' : 'text-green-600', 'sub' => 'Failed/stale'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(420px,0.9fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Queue & SLA Operasional</h2>
                    <p class="text-sm text-gray-500">Indikator ini dipakai untuk memastikan request ESS dan job background tidak macet.</p>
                </div>
                <div class="group relative">
                    <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                    <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                        Queue backlog berarti job background belum diproses worker. Failed jobs perlu dicek root cause sebelum retry. SLA breach berarti approval cuti/lembur melewati batas operasional sehingga bisa mengganggu ESS dan payroll.
                    </div>
                </div>
            </div>
            <div class="grid gap-4 p-5 md:grid-cols-2">
                @foreach([
                    ['title' => 'Queue Pending', 'value' => $queue['pending_jobs'], 'note' => 'Oldest age '.$queue['oldest_age_minutes'].' menit', 'status' => $queue['status']],
                    ['title' => 'Failed Jobs', 'value' => $queue['failed_jobs'], 'note' => 'Failed jobs 7 hari terakhir', 'status' => $queue['failed_jobs'] > 0 ? 'warning' : 'healthy'],
                    ['title' => 'Cuti Lewat SLA', 'value' => $sla['pending_leave_over_24h'], 'note' => 'Pending lebih dari 24 jam', 'status' => $sla['pending_leave_over_24h'] > 0 ? 'warning' : 'healthy'],
                    ['title' => 'Lembur Lewat SLA', 'value' => $sla['pending_overtime_over_12h'], 'note' => 'Pending lebih dari 12 jam', 'status' => $sla['pending_overtime_over_12h'] > 0 ? 'warning' : 'healthy'],
                    ['title' => 'Notifikasi Queued', 'value' => $notifications['queued'], 'note' => $notifications['stale'].' stale lebih dari 2 jam', 'status' => $notifications['stale'] > 0 ? 'warning' : 'healthy'],
                    ['title' => 'Notifikasi Failed', 'value' => $notifications['failed'], 'note' => $notifications['sent_7d'].' terkirim 7 hari terakhir', 'status' => $notifications['failed'] > 0 ? 'warning' : 'healthy'],
                ] as $item)
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $item['title'] }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $item['note'] }}</div>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass($item['status']) }}">{{ $item['status'] === 'healthy' ? 'Sehat' : 'Review' }}</span>
                        </div>
                        <div class="mt-4 text-3xl font-semibold {{ $item['status'] === 'healthy' ? 'text-green-600' : 'text-amber-600' }}">{{ $item['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Action Item</h2>
                <p class="text-sm text-gray-500">Arahan follow-up untuk admin sistem dan HR operation.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($actions as $index => $action)
                    <div class="flex gap-3 px-5 py-4">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">{{ $index + 1 }}</span>
                        <div class="text-sm leading-6 text-gray-700">{{ $action }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Health Connector</h2>
            <p class="text-sm text-gray-500">Status konektor yang berdampak ke absensi, payroll, tax, kalender, dan komunikasi karyawan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Connector</th>
                        <th class="px-5 py-3 text-left">Aktif</th>
                        <th class="px-5 py-3 text-left">Last Seen</th>
                        <th class="px-5 py-3 text-left">Issue</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($connectors as $connector)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $connector['name'] }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $connector['active'] }} / {{ $connector['total'] }}</td>
                            <td class="px-5 py-4 text-gray-500">{{ $connector['last_seen'] ? \Carbon\Carbon::parse($connector['last_seen'])->format('d M Y H:i') : '-' }}</td>
                            <td class="px-5 py-4 font-semibold {{ $connector['issue'] > 0 ? 'text-amber-600' : 'text-green-600' }}">{{ $connector['issue'] }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass($connector['status']) }}">{{ $connector['label'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route($connector['route']) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @if($queue['failed_by_queue']->isNotEmpty())
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Failed Job per Queue</h2>
                <p class="text-sm text-gray-500">Prioritaskan queue dengan failed job tertinggi.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($queue['failed_by_queue'] as $item)
                    <div class="flex items-center justify-between px-5 py-4">
                        <div class="font-semibold text-gray-900">{{ $item->queue }}</div>
                        <div class="text-lg font-semibold text-red-600">{{ $item->total }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
