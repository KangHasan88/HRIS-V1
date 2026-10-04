@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">WhatsApp Gateway</h1>
            <p class="mt-1 text-sm text-gray-500">Gateway untuk approval, reminder, notifikasi slip gaji, dan callback status pengiriman.</p>
        </div>
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
                ['label' => 'Gateway', 'value' => $summary['gateways'], 'color' => 'text-gray-900'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Queued', 'value' => $summary['queued'], 'color' => 'text-blue-600'],
                ['label' => 'Failed', 'value' => $summary['failed'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Gateway</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan WhatsApp Gateway',
                        'description' => 'Gateway menyimpan konfigurasi provider WhatsApp untuk mengirim approval, reminder, slip gaji, dan notifikasi HRIS lain.',
                        'steps' => [
                            'Pilih provider dan tipe koneksi yang dipakai.',
                            'Aktifkan event yang boleh dikirim melalui gateway ini.',
                            'Isi token dalam bentuk masked token dulu; secret real masuk tahap hardening credential.',
                        ],
                        'notes' => [
                            'Simulasi webhook tersedia agar workflow status bisa diuji sebelum provider real tersambung.',
                            'Pesan masuk antrean, lalu worker/provider akan memproses di tahap integrasi berikutnya.',
                        ],
                    ])
                </div>
            </div>

            <form action="{{ route('integrations.whatsapp-gateway.settings') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Gateway</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Gateway</label>
                            <input name="gateway_code" required placeholder="Contoh: WA-HO-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Gateway</label>
                            <input name="gateway_name" required placeholder="Contoh: WhatsApp HR HO" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Provider</label>
                            <select name="provider" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="meta_cloud_api">Meta Cloud API</option>
                                <option value="wablas">Wablas</option>
                                <option value="qontak">Qontak</option>
                                <option value="fonnte">Fonnte</option>
                                <option value="generic_webhook">Generic Webhook</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Koneksi</label>
                            <select name="connection_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="cloud_api">Cloud API</option>
                                <option value="webhook_queue">Webhook Queue</option>
                                <option value="local_bridge">Local Bridge</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Endpoint URL</label>
                            <input name="endpoint_url" placeholder="https://provider/api/send" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sender ID</label>
                            <input name="sender_id" placeholder="Nomor atau sender provider" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Auth Mode</label>
                            <select name="auth_mode" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="bearer_token">Bearer Token</option>
                                <option value="api_key">API Key</option>
                                <option value="basic_auth">Basic Auth</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="paused">Paused</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Scope & Event</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Semua perusahaan</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                        <select name="branch_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Semua cabang/site</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach($events as $value => $label)
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                <input type="checkbox" name="enabled_events[]" value="{{ $value }}" checked class="rounded border-gray-300 text-blue-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <input name="masked_token" placeholder="Masked token, contoh: wa_xxx_****" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="notes" placeholder="Catatan gateway" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                    <i class="fas fa-save"></i> Simpan Gateway
                </button>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Queue Pesan</h2>
                <p class="text-sm text-gray-500">Simulasikan pesan approval, reminder, slip gaji, atau notifikasi manual.</p>
            </div>

            <form action="{{ route('integrations.whatsapp-gateway.messages') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="whatsapp_gateway_setting_id" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm sm:col-span-2">
                        <option value="">Pilih gateway</option>
                        @foreach($gateways as $gateway)
                            <option value="{{ $gateway->id }}">{{ $gateway->gateway_code }} - {{ $gateway->gateway_name }}</option>
                        @endforeach
                    </select>
                    <input name="message_number" required placeholder="Nomor pesan, contoh: WA-MSG-001" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="event_key" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @foreach($events as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input name="recipient_name" placeholder="Nama penerima" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="recipient_phone" required placeholder="62812..." class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="template_name" placeholder="Nama template provider" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="priority" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="low">Low</option>
                    </select>
                    <input name="source_type" placeholder="Source type, contoh: payslip" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input type="number" name="source_id" placeholder="Source ID opsional" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <textarea name="message_body" required rows="4" placeholder="Isi pesan WhatsApp" class="resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm sm:col-span-2"></textarea>
                    <input type="datetime-local" name="scheduled_at" class="rounded-lg border border-gray-300 px-3 py-2 text-sm sm:col-span-2">
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                    <i class="fas fa-paper-plane"></i> Queue Pesan
                </button>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Outbox & Webhook</h2>
            <p class="text-sm text-gray-500">Pantau antrean pesan dan simulasi callback provider.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Pesan</th>
                        <th class="px-5 py-3">Penerima</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($messages as $message)
                        <tr>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $message->message_number }}</div>
                                <div class="max-w-md truncate text-xs text-gray-500">{{ $message->message_body }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $message->recipient_name ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $message->recipient_phone }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $events[$message->event_key] ?? $message->event_key }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($message->status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <form action="{{ route('integrations.whatsapp-gateway.webhook', $message) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <select name="delivery_status" class="rounded-lg border border-gray-300 px-2 py-1 text-xs">
                                        <option value="sent">Sent</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="read">Read</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                    <button class="rounded-lg bg-blue-600 px-3 py-1 text-xs font-semibold text-white">Simulasi</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada pesan WhatsApp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
