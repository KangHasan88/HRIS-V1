@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Offline Queue ESS</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau draft clock-in/out dan pengajuan ESS yang tersimpan saat device offline.</p>
        </div>
        <a href="{{ route('settings.mobile-bridge') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">
            <i class="fas fa-code"></i> Mobile Bridge
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Queued', 'value' => $summary['queued'], 'color' => 'text-blue-600'],
                ['label' => 'Synced', 'value' => $summary['synced'], 'color' => 'text-green-600'],
                ['label' => 'Failed', 'value' => $summary['failed'], 'color' => 'text-red-600'],
                ['label' => 'Clock Draft', 'value' => $summary['clock'], 'color' => 'text-amber-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-2 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Tambah Queue Offline</h2>
                <p class="mt-1 text-sm text-gray-500">Dipakai untuk simulasi payload WebView ketika mobile kehilangan koneksi.</p>
            </div>
            @include('partials.form-help', [
                'title' => 'Panduan Offline Queue ESS',
                'description' => 'Offline queue menyimpan event atau draft pengajuan yang dibuat saat device tidak terkoneksi.',
                'steps' => [
                    'Pilih karyawan dan tipe queue sesuai data yang tertahan di device.',
                    'Isi nomor queue dari device agar tidak diproses ganda.',
                    'Payload key/value mewakili data utama yang nanti disinkronkan.',
                    'Gunakan Sync jika payload sudah diterima server, atau Failed jika perlu dikoreksi device/user.',
                ],
                'notes' => [
                    'Clock event offline belum otomatis masuk absensi sampai status queue disinkronkan.',
                    'Draft pengajuan tetap bisa diretry tanpa menghapus riwayat audit.',
                ],
            ])
        </div>

        <form method="POST" action="{{ route('ess.offline-queue.store') }}" class="mt-5 space-y-4">
            @csrf
            <section class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Identitas Queue</h3>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">Karyawan</span>
                        <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Pilih karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">Nomor Queue</span>
                        <input name="queue_number" required placeholder="Contoh: OFF-CLK-050" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">Tipe Data</span>
                        <select name="item_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="clock_event">Clock-in/out offline</option>
                            <option value="leave_draft">Draft cuti</option>
                            <option value="overtime_draft">Draft lembur</option>
                            <option value="business_trip_draft">Draft dinas</option>
                            <option value="attendance_correction_draft">Draft koreksi absensi</option>
                        </select>
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">Status Queue</span>
                        <select name="queue_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="queued">Queued</option>
                            <option value="failed">Failed</option>
                            <option value="syncing">Syncing</option>
                        </select>
                    </label>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Device & Payload</h3>
                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">Waktu Capture</span>
                        <input type="datetime-local" name="captured_at" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">Device UID</span>
                        <input name="device_uid" placeholder="Hash device / uuid" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">Nama Device</span>
                        <input name="device_name" placeholder="Contoh: Android Ayu" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">Platform</span>
                        <select name="platform" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="mobile_webview">Mobile WebView</option>
                            <option value="android_webview">Android WebView</option>
                            <option value="ios_webview">iOS WebView</option>
                            <option value="browser">Browser</option>
                        </select>
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">App Version</span>
                        <input name="app_version" placeholder="Contoh: 1.0.0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="space-y-1">
                        <span class="text-xs font-semibold uppercase text-gray-500">Payload Key</span>
                        <input name="payload_key" placeholder="Contoh: event_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="space-y-1 lg:col-span-2">
                        <span class="text-xs font-semibold uppercase text-gray-500">Payload Value</span>
                        <input name="payload_value" placeholder="Contoh: clock_in / leave_annual" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="space-y-1 lg:col-span-2">
                        <span class="text-xs font-semibold uppercase text-gray-500">Catatan Payload</span>
                        <textarea name="notes" rows="3" placeholder="Konteks offline, GPS, atau draft request." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </label>
                    <label class="space-y-1 lg:col-span-2">
                        <span class="text-xs font-semibold uppercase text-gray-500">Alasan Gagal</span>
                        <textarea name="failure_reason" rows="3" placeholder="Isi jika queue dibuat dalam status failed." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                    </label>
                </div>
            </section>

            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white">
                    <i class="fas fa-cloud-arrow-up"></i> Simpan Queue
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Offline Queue</h2>
            <p class="text-sm text-gray-500">Status sinkronisasi draft dari mobile ESS.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Queue</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3">Device</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Retry</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        <tr>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $item->queue_number }}</div>
                                <div class="text-xs text-gray-500">{{ optional($item->captured_at)->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900">{{ $item->employeeProfile?->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->employeeProfile?->employee_number }}</div>
                            </td>
                            <td class="px-5 py-4">{{ str_replace('_', ' ', $item->item_type) }}</td>
                            <td class="px-5 py-4">
                                <div>{{ $item->device_name ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->platform }} {{ $item->app_version }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $item->queue_status === 'synced' ? 'bg-green-50 text-green-700' : ($item->queue_status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700') }}">{{ ucfirst($item->queue_status) }}</span>
                            </td>
                            <td class="px-5 py-4">{{ $item->retry_count }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('ess.offline-queue.sync', $item) }}">
                                        @csrf
                                        <button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Sync</button>
                                    </form>
                                    <form method="POST" action="{{ route('ess.offline-queue.fail', $item) }}" class="flex gap-2">
                                        @csrf
                                        <input name="failure_reason" placeholder="Alasan gagal" class="w-32 rounded-lg border border-gray-300 px-2 py-2 text-xs">
                                        <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">Fail</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-500">Belum ada offline queue.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
