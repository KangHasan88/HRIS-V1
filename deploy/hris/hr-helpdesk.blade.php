@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Helpdesk HR</h1>
            <p class="mt-1 text-sm text-gray-500">Monitor tiket karyawan, SLA, assignment HR, dan percakapan follow-up.</p>
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
                ['label' => 'Baru', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
                ['label' => 'Diproses', 'value' => $summary['in_progress'], 'color' => 'text-indigo-600'],
                ['label' => 'Lewat SLA', 'value' => $summary['overdue'], 'color' => 'text-red-600'],
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
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Queue Tiket</h2>
                @include('partials.form-help', [
                    'title' => 'Panduan Queue Helpdesk',
                    'description' => 'Halaman ini dipakai HR untuk memproses tiket karyawan, mengatur PIC, menjaga SLA, dan membalas percakapan.',
                    'steps' => [
                        'Review kategori, prioritas, dan SLA tiket.',
                        'Assign PIC HR dan ubah status sesuai progres.',
                        'Isi balasan yang tampil ke karyawan atau catatan internal khusus HR.',
                    ],
                    'notes' => [
                        'Catatan internal tidak tampil di ESS karyawan.',
                        'Status selesai/ditutup mencatat waktu resolved untuk audit.',
                    ],
                ])
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($tickets as $ticket)
                <div class="p-5">
                    <div class="grid gap-5 xl:grid-cols-[1fr_420px]">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-gray-900">{{ $ticket->ticket_number }}</span>
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $categories[$ticket->category] ?? $ticket->category }}</span>
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ $statuses[$ticket->status] ?? $ticket->status }}</span>
                                <span class="rounded-full bg-yellow-50 px-2 py-1 text-xs font-semibold text-yellow-700">{{ $priorities[$ticket->priority] ?? $ticket->priority }}</span>
                            </div>
                            <div class="mt-2 text-lg font-semibold text-gray-900">{{ $ticket->subject }}</div>
                            <div class="mt-1 text-sm text-gray-500">
                                {{ $ticket->employeeProfile?->employee_number }} - {{ $ticket->employeeProfile?->full_name }}
                                · {{ $ticket->employeeProfile?->company?->code ?: '-' }}
                                · {{ $ticket->employeeProfile?->branch?->code ?: '-' }}
                            </div>
                            <div class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">First Response</div>
                                    <div class="mt-1 font-semibold text-gray-900">{{ $ticket->first_response_due_at?->format('d M H:i') ?: '-' }}</div>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">SLA Selesai</div>
                                    <div class="mt-1 font-semibold {{ $ticket->sla_due_at && $ticket->sla_due_at->isPast() && ! in_array($ticket->status, ['resolved', 'closed'], true) ? 'text-red-600' : 'text-gray-900' }}">{{ $ticket->sla_due_at?->format('d M H:i') ?: '-' }}</div>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">PIC HR</div>
                                    <div class="mt-1 font-semibold text-gray-900">{{ $ticket->assignee?->name ?: 'Belum assign' }}</div>
                                </div>
                            </div>

                            <div class="mt-4 space-y-2">
                                @foreach($ticket->messages->take(-5) as $message)
                                    <div class="rounded-xl {{ $message->is_internal ? 'border border-dashed border-yellow-200 bg-yellow-50' : ($message->sender_type === 'employee' ? 'bg-blue-50' : 'bg-gray-50') }} px-3 py-2 text-sm">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            {{ $message->is_internal ? 'Internal HR' : ($message->sender_type === 'employee' ? 'Karyawan' : 'HR') }}
                                            · {{ $message->sender?->name ?: '-' }}
                                            · {{ $message->created_at?->format('d M H:i') }}
                                        </div>
                                        <div class="mt-1 text-gray-800">{{ $message->message_body }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <form action="{{ route('hris.helpdesk.update', $ticket) }}" method="POST" class="rounded-xl border border-gray-200 bg-white p-4">
                            @csrf
                            <h3 class="text-sm font-semibold text-gray-900">Update Tiket</h3>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                                    <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected($ticket->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">PIC HR</label>
                                    <select name="assigned_to" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <option value="">HR Queue</option>
                                        @foreach($hrUsers as $user)
                                            <option value="{{ $user->id }}" @selected($ticket->assigned_to === $user->id)>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Balasan ke Karyawan</label>
                                    <textarea name="hr_reply" rows="3" placeholder="Isi balasan yang akan tampil di ESS karyawan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Internal HR</label>
                                    <textarea name="internal_note" rows="2" placeholder="Opsional, tidak tampil di sisi karyawan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                                </div>
                            </div>
                            <button class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                <i class="fas fa-save"></i>
                                Simpan Update
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada tiket helpdesk.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
