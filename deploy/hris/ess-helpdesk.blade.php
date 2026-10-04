@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Helpdesk HR</h1>
            <p class="mt-1 text-sm text-gray-500">Kirim pertanyaan atau kendala HR, pantau SLA, dan lanjutkan chat dengan tim HR.</p>
        </div>
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
                ['label' => 'Total Tiket', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Sedang Diproses', 'value' => $summary['open'], 'color' => 'text-yellow-600'],
                ['label' => 'Menunggu Saya', 'value' => $summary['waiting'], 'color' => 'text-indigo-600'],
                ['label' => 'Lewat SLA', 'value' => $summary['overdue'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    @if(! $employee)
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-sm text-yellow-800 shadow-sm">
            <div class="font-semibold">Akun login belum terhubung ke profil karyawan.</div>
            <p class="mt-1">Halaman sudah siap, tapi tiket helpdesk baru aktif setelah HR/admin menghubungkan akun ini ke profil karyawan.</p>
        </div>
    @else
        <div class="grid gap-5 xl:grid-cols-[.85fr_1.15fr]">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900">Buat Tiket</h2>
                        @include('partials.form-help', [
                            'title' => 'Panduan Helpdesk HR',
                            'description' => 'Form ini dipakai karyawan untuk meminta bantuan HR terkait data karyawan, absensi, cuti, payroll, dokumen, atau akses ESS.',
                            'steps' => [
                                'Pilih kategori dan prioritas sesuai dampak kendala.',
                                'Tulis judul singkat, detail kendala, dan referensi lampiran jika ada.',
                                'Setelah dikirim, HR akan membalas melalui thread chat tiket ini.',
                            ],
                            'notes' => [
                                'SLA dihitung dari prioritas: urgent lebih cepat dari normal.',
                                'Balasan HR yang bersifat internal tidak tampil di sisi karyawan.',
                            ],
                        ])
                    </div>
                    <p class="text-sm text-gray-500">{{ $employee->employee_number }} - {{ $employee->full_name }}</p>
                </div>

                <form action="{{ route('ess.helpdesk.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Identitas Tiket</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Tiket</label>
                                <input name="ticket_number" required placeholder="Contoh: HRD-HELP-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Prioritas</label>
                                <select name="priority" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach($priorities as $value => $label)
                                        <option value="{{ $value }}" @selected($value === 'normal')>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</label>
                                <select name="category" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach($categories as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-gray-900">Detail Kendala</h3>
                        <div class="mt-3 space-y-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Judul Tiket</label>
                                <input name="subject" required placeholder="Contoh: Data alamat perlu diperbaiki" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Deskripsi</label>
                                <textarea name="description" required rows="4" placeholder="Jelaskan kendala, tanggal kejadian, dan bantuan yang dibutuhkan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi Lampiran</label>
                                <input name="attachment_reference" placeholder="Opsional: file id, URL, atau nomor dokumen" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                    </div>

                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-headset"></i>
                        Kirim Tiket
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Riwayat Tiket</h2>
                    <p class="text-sm text-gray-500">Tiket dan percakapan yang dibuat dari akun login ini.</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <div class="p-5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold text-gray-900">{{ $ticket->ticket_number }}</span>
                                        <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $categories[$ticket->category] ?? $ticket->category }}</span>
                                        <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ $statuses[$ticket->status] ?? $ticket->status }}</span>
                                    </div>
                                    <div class="mt-2 font-semibold text-gray-900">{{ $ticket->subject }}</div>
                                    <div class="mt-1 text-xs text-gray-500">SLA: {{ $ticket->sla_due_at?->format('d M Y H:i') ?: '-' }} · Assigned: {{ $ticket->assignee?->name ?: 'HR Queue' }}</div>
                                </div>
                                <div class="text-sm font-semibold {{ $ticket->sla_due_at && $ticket->sla_due_at->isPast() && ! in_array($ticket->status, ['resolved', 'closed'], true) ? 'text-red-600' : 'text-gray-500' }}">
                                    {{ $priorities[$ticket->priority] ?? $ticket->priority }}
                                </div>
                            </div>

                            <div class="mt-4 space-y-2">
                                @foreach($ticket->messages->where('is_internal', false)->take(-4) as $message)
                                    <div class="rounded-xl {{ $message->sender_type === 'employee' ? 'bg-blue-50 text-blue-950' : 'bg-gray-50 text-gray-800' }} px-3 py-2 text-sm">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $message->sender_type === 'employee' ? 'Karyawan' : 'HR' }} · {{ $message->created_at?->format('d M H:i') }}</div>
                                        <div class="mt-1">{{ $message->message_body }}</div>
                                    </div>
                                @endforeach
                            </div>

                            @if(! in_array($ticket->status, ['closed'], true))
                                <form action="{{ route('ess.helpdesk.reply', $ticket) }}" method="POST" class="mt-4 grid gap-3 lg:grid-cols-[1fr_220px_120px]">
                                    @csrf
                                    <input name="message_body" required placeholder="Tulis balasan atau informasi tambahan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <input name="attachment_reference" placeholder="Lampiran opsional" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Balas</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada tiket helpdesk.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
