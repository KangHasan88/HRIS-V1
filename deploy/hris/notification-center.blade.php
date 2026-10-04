@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Pusat Notifikasi</h1>
            <p class="mt-1 text-sm text-gray-500">Template dan antrean notifikasi untuk in-app, email, WhatsApp, dan push.</p>
        </div>
        <a href="{{ route('settings.approval-workflows') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-route"></i>
            Workflow Approval
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
                ['label' => 'Template', 'value' => $summary['templates'], 'color' => 'text-blue-600'],
                ['label' => 'Queued', 'value' => $summary['queued'], 'color' => 'text-yellow-600'],
                ['label' => 'Sent', 'value' => $summary['sent'], 'color' => 'text-green-600'],
                ['label' => 'Failed', 'value' => $summary['failed'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Buat Template</h2>
                <p class="text-sm text-gray-500">Gunakan variable seperti @{{name}} dan @{{deadline}}.</p>
            </div>
            <form action="{{ route('settings.notification-center.templates') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <input name="name" required placeholder="Nama template" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="event_key" required placeholder="approval.leave.submitted" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <div class="grid grid-cols-2 gap-3 text-sm text-gray-600">
                    @foreach(['in_app' => 'In-app', 'email' => 'Email', 'whatsapp' => 'WhatsApp', 'push' => 'Push'] as $value => $label)
                        <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2">
                            <input type="checkbox" name="channels[]" value="{{ $value }}" class="rounded border-gray-300 text-blue-600" {{ $value === 'in_app' ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <input name="subject" placeholder="Subject" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <textarea name="body_template" required rows="4" placeholder="Halo @{{name}}, approval kamu menunggu review." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <input name="variables" placeholder="name,deadline,module" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_enabled" value="1" checked class="rounded border-gray-300 text-blue-600">
                    Template aktif
                </label>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-file-circle-plus"></i>
                    Simpan Template
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Queue Notifikasi</h2>
                <p class="text-sm text-gray-500">Simulasikan antrean notifikasi untuk worker/provider.</p>
            </div>
            <form action="{{ route('settings.notification-center.deliveries') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <select name="notification_template_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih template</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->code }} - {{ $template->name }}</option>
                    @endforeach
                </select>
                <select name="user_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Recipient manual</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 md:grid-cols-2">
                    <input name="recipient_name" placeholder="Nama manual" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="recipient_email" placeholder="email@domain.com" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="recipient_phone" placeholder="62812..." class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="priority" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="low">Low</option>
                    </select>
                    <input name="payload_key" placeholder="deadline" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="payload_value" placeholder="20 Jul 2026" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input type="datetime-local" name="scheduled_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-paper-plane"></i>
                    Queue Notifikasi
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Antrean Terbaru</h2>
            <p class="text-sm text-gray-500">Status delivery per channel dan payload rendering.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Recipient</th>
                        <th class="px-5 py-3">Channel</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Message</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($deliveries as $delivery)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $delivery->event_key }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $delivery->recipient_name ?? $delivery->user?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $delivery->recipient_email ?? $delivery->user?->email }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ implode(', ', $delivery->channels ?? []) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ ucfirst($delivery->priority) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ Str::limit($delivery->rendered_message, 80) }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($delivery->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada antrean notifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
