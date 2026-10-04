@extends('layouts.sidebar')

@section('title', 'Public API & Webhook')

@section('content')
@php
    $statusClass = fn ($status) => [
        'ready' => 'bg-green-50 text-green-700',
        'guarded' => 'bg-amber-50 text-amber-700',
        'review' => 'bg-amber-50 text-amber-700',
    ][$status] ?? 'bg-gray-100 text-gray-600';
    $methodClass = fn ($method) => [
        'GET' => 'bg-blue-50 text-blue-700',
        'POST' => 'bg-purple-50 text-purple-700',
    ][$method] ?? 'bg-gray-100 text-gray-700';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Public API & Webhook</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola katalog endpoint, event webhook, API key policy, rate limit, dan audit log integrasi.</p>
        </div>
        <a href="{{ route('settings.integration-health') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-heart-pulse mr-2"></i> Health Integrasi
        </a>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Readiness', 'value' => $summary['readiness'].'%', 'color' => $summary['readiness'] >= 85 ? 'text-green-600' : 'text-amber-600', 'sub' => $readiness['message']],
                ['label' => 'Endpoint', 'value' => $summary['endpoints'], 'color' => 'text-gray-900', 'sub' => $summary['enabled_endpoints'].' ready'],
                ['label' => 'Webhook Event', 'value' => $summary['webhooks'], 'color' => 'text-blue-600', 'sub' => 'Catalog event'],
                ['label' => 'Recent Logs', 'value' => $summary['recent_logs'], 'color' => 'text-gray-900', 'sub' => 'Audit integration'],
                ['label' => 'Failed Jobs', 'value' => $summary['failed_jobs'], 'color' => $summary['failed_jobs'] > 0 ? 'text-red-600' : 'text-green-600', 'sub' => '7 hari terakhir'],
                ['label' => 'Guarded', 'value' => $readiness['guarded_count'], 'color' => $readiness['guarded_count'] > 0 ? 'text-amber-600' : 'text-green-600', 'sub' => 'Sensitive endpoint'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="line-clamp-2 text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(420px,0.85fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Endpoint Catalog</h2>
                    <p class="text-sm text-gray-500">Daftar endpoint yang disiapkan untuk integrasi internal, partner, dan BI.</p>
                </div>
                <div class="group relative">
                    <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                    <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                        Endpoint ready berarti secara desain siap dipakai setelah API key dan allowlist aktif. Endpoint guarded berisi data sensitif atau aksi tulis, sehingga wajib ada approval security, HMAC/idempotency, dan audit log lengkap.
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Endpoint</th>
                            <th class="px-5 py-3 text-left">Module</th>
                            <th class="px-5 py-3 text-left">Scope</th>
                            <th class="px-5 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($endpoints as $endpoint)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $methodClass($endpoint['method']) }}">{{ $endpoint['method'] }}</span>
                                        <span class="font-mono text-xs font-semibold text-gray-900">{{ $endpoint['path'] }}</span>
                                    </div>
                                    <div class="mt-2 font-semibold text-gray-900">{{ $endpoint['code'] }}</div>
                                    <div class="mt-1 text-xs leading-5 text-gray-500">{{ $endpoint['note'] }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-700">{{ $endpoint['module'] }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $endpoint['scope'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass($endpoint['status']) }}">{{ $endpoint['status'] === 'ready' ? 'Ready' : 'Guarded' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Action Item</h2>
                <p class="text-sm text-gray-500">Minimum hardening sebelum API/webhook production.</p>
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

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Webhook Event</h2>
                <p class="text-sm text-gray-500">Event outbound/inbound yang perlu didukung connector.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($webhooks as $event)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-mono text-sm font-semibold text-gray-900">{{ $event['event'] }}</div>
                            <div class="mt-1 text-sm text-gray-500">{{ $event['module'] }} · {{ $event['delivery'] }}</div>
                            <div class="mt-1 text-xs text-gray-400">{{ $event['payload'] }}</div>
                        </div>
                        <span class="w-max rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass($event['status']) }}">{{ $event['status'] === 'ready' ? 'Ready' : 'Guarded' }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Rate Limit & Auth Policy</h2>
                <p class="text-sm text-gray-500">Tier akses yang disarankan untuk API key dan webhook.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($rateLimits as $policy)
                    <div class="px-5 py-4">
                        <div class="font-semibold text-gray-900">{{ $policy['tier'] }}</div>
                        <div class="mt-2 grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
                            <div><span class="font-semibold text-gray-900">Limit:</span> {{ $policy['limit'] }}</div>
                            <div><span class="font-semibold text-gray-900">Burst:</span> {{ $policy['burst'] }}</div>
                            <div class="sm:col-span-2"><span class="font-semibold text-gray-900">Auth:</span> {{ $policy['auth'] }}</div>
                            <div class="sm:col-span-2 text-xs text-gray-500">{{ $policy['use_case'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Log Integrasi Terbaru</h2>
            <p class="text-sm text-gray-500">Audit log API, webhook, Coretax, biometric, dan integrasi terkait.</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($logs as $log)
                <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                    <div>
                        <div class="font-mono text-sm font-semibold text-gray-900">{{ $log['event'] }}</div>
                        <div class="mt-1 text-sm text-gray-600">{{ $log['summary'] }}</div>
                        <div class="mt-1 text-xs text-gray-400">{{ $log['actor'] }} · {{ $log['ip'] }}</div>
                    </div>
                    <div class="text-xs text-gray-500">{{ $log['created_at']?->format('d M Y H:i') }}</div>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada audit log integrasi.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
