@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Audit Trail</h1>
            <p class="mt-1 text-sm text-gray-500">Riwayat perubahan penting untuk akses, organisasi, dan konfigurasi modul.</p>
        </div>
        <a href="{{ route('settings.users') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-users-gear"></i>
            User Access
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-3">
            @foreach([
                ['label' => 'Total Log', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Hari Ini', 'value' => $summary['today'], 'color' => 'text-green-600'],
                ['label' => 'Jenis Event', 'value' => $summary['events'], 'color' => 'text-yellow-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Log Terbaru</h2>
            <p class="text-sm text-gray-500">Maksimal 100 aktivitas terakhir.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Actor</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Summary</th>
                        <th class="px-5 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-gray-600">
                                {{ $log->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $log->actor?->name ?? 'System' }}</div>
                                <div class="text-xs text-gray-500">{{ $log->actor?->email ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ $log->event }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-700">{{ $log->summary }}</td>
                            <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $log->ip_address ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
