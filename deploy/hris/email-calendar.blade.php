@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Email & Kalender</h1>
            <p class="mt-1 text-sm text-gray-500">Connector untuk undangan interview, jadwal training, dan kalender cuti.</p>
        </div>
        <a href="{{ route('integrations.biometric-connectors') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-fingerprint"></i> Biometric Connector
        </a>
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
                ['label' => 'Connector', 'value' => $summary['connectors'], 'color' => 'text-gray-900'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Email Queued', 'value' => $summary['queued_email'], 'color' => 'text-blue-600'],
                ['label' => 'Calendar Queued', 'value' => $summary['queued_calendar'], 'color' => 'text-amber-600'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Connector</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai connector email dan kalender',
                        'description' => 'Connector menyimpan konfigurasi provider email/kalender yang dipakai HRIS untuk mengirim undangan dan membuat event.',
                        'steps' => [
                            'Pilih provider seperti Google Workspace, Microsoft 365, SMTP ICS, atau webhook generic.',
                            'Isi sender email dan calendar owner untuk identitas pengirim.',
                            'Centang modul yang boleh memakai connector ini: interview, training, atau cuti.',
                            'Set status Active jika siap dipakai untuk queue event.',
                        ],
                        'notes' => [
                            'Token/OAuth real akan dipasang di tahap hardening connector.',
                            'Saat ini event masuk antrean agar workflow dan payload bisa diuji dulu.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Satu connector bisa dipakai lintas modul sesuai scope perusahaan/site.</p>
            </div>

            <form action="{{ route('integrations.email-calendar.connectors') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Connector</h3>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Connector</label>
                            <input name="connector_code" required placeholder="Contoh: CAL-HO-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Connector</label>
                            <input name="connector_name" required placeholder="Contoh: HR Calendar HO" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Provider</label>
                            <select name="provider" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="google_workspace">Google Workspace</option>
                                <option value="microsoft_365">Microsoft 365</option>
                                <option value="smtp_ics">SMTP + ICS</option>
                                <option value="generic_webhook">Generic Webhook</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Koneksi</label>
                            <select name="connection_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="oauth_api">OAuth API</option>
                                <option value="smtp_ics">SMTP + ICS</option>
                                <option value="webhook">Webhook</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Pengirim & Scope</h3>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sender Email</label>
                            <input type="email" name="sender_email" placeholder="hr@company.com" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Calendar Owner</label>
                            <input name="calendar_owner" placeholder="HR Calendar / email kalender" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Semua perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                            <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Semua cabang/site</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Auth Mode</label>
                            <select name="auth_mode" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="oauth">OAuth</option>
                                <option value="api_key">API Key</option>
                                <option value="basic_auth">Basic Auth</option>
                                <option value="smtp_auth">SMTP Auth</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Timezone</label>
                            <input name="default_timezone" value="Asia/Jakarta" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Endpoint / Webhook</label>
                            <input name="endpoint_url" placeholder="Opsional untuk webhook/API" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
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
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        @foreach(['interview' => 'Interview', 'training' => 'Training', 'leave' => 'Cuti'] as $value => $label)
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="enabled_modules[]" value="{{ $value }}" checked class="rounded border-gray-300 text-blue-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                        <input name="notes" placeholder="Catatan koneksi, owner, atau policy pengiriman." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Connector
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Queue Event</h2>
                <p class="text-sm text-gray-500">Masukkan undangan ke antrean email dan kalender.</p>
            </div>

            <form action="{{ route('integrations.email-calendar.events') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Sumber Event</h3>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Connector</label>
                            <select name="email_calendar_connector_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih connector</option>
                                @foreach($connectors as $connector)
                                    <option value="{{ $connector->id }}">{{ $connector->connector_code }} - {{ $connector->connector_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Event</label>
                            <select name="source_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="interview">Interview</option>
                                <option value="training">Training</option>
                                <option value="leave">Cuti</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Source ID</label>
                            <input type="number" name="source_id" placeholder="Opsional: ID interview/training/cuti" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Event</label>
                            <input name="event_number" required placeholder="Contoh: EVT-087-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Judul Manual</label>
                            <input name="event_title" placeholder="Opsional jika source dipilih" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Manual</label>
                            <input type="datetime-local" name="scheduled_start" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Selesai Manual</label>
                            <input type="datetime-local" name="scheduled_end" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Penerima Manual</h3>
                    <div class="mt-3 grid gap-3 lg:grid-cols-2">
                        <input name="recipient_name" placeholder="Nama penerima opsional" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input type="email" name="recipient_email" placeholder="email@domain.com" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="location_or_link" placeholder="Lokasi / link meeting" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm lg:col-span-2">
                    </div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="send_email" value="1" checked class="rounded border-gray-300 text-blue-600">
                            Kirim email
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="create_calendar_event" value="1" checked class="rounded border-gray-300 text-blue-600">
                            Buat event kalender
                        </label>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-paper-plane"></i> Queue Event
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Monitoring Event</h2>
            <p class="text-sm text-gray-500">Status antrean email dan kalender dari semua connector.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Sumber</th>
                        <th class="px-5 py-3">Jadwal</th>
                        <th class="px-5 py-3">Penerima</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Connector</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($events as $event)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $event->event_title }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $event->event_number }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ ucfirst($event->source_type) }} #{{ $event->source_id ?: '-' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600">
                                <div>{{ $event->scheduled_start?->format('d M Y H:i') }}</div>
                                <div>{{ $event->scheduled_end?->format('d M Y H:i') ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">
                                <div>{{ $event->recipient_name ?: '-' }}</div>
                                <div>{{ $event->recipient_email ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">Email: {{ ucfirst($event->email_status) }}</span>
                                    <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Cal: {{ ucfirst($event->calendar_status) }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $event->connector?->connector_code }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Belum ada event email/kalender.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
