@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Kalender Compliance Pajak</h1>
            <p class="mt-1 text-sm text-gray-500">Deadline PPh 21, reminder, SLA, eskalasi, dan evidence readiness untuk payroll tax.</p>
        </div>
        <a href="{{ route('payroll.coretax-connector') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-plug"></i>
            Coretax Connector
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
                ['label' => 'Event', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Due 7 Hari', 'value' => $summary['due_soon'], 'color' => 'text-yellow-600'],
                ['label' => 'Overdue', 'value' => $summary['overdue'], 'color' => 'text-red-600'],
                ['label' => 'Escalated', 'value' => $summary['escalated'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="border-b border-gray-100 pb-4">
            <h2 class="text-lg font-semibold text-gray-900">Tambah Deadline</h2>
            <p class="text-sm text-gray-500">Buat jadwal compliance dengan reminder otomatis dan rule eskalasi SLA.</p>
        </div>
        <form action="{{ route('payroll.tax-compliance-calendar.store') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-4">
            @csrf
            <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Semua company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                @endforeach
            </select>
            <select name="event_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="monthly_pph21">PPh 21 Bulanan</option>
                <option value="annual_pph21">PPh 21 Tahunan</option>
                <option value="coretax_submission">Submit Coretax</option>
                <option value="bukti_potong">Bukti Potong</option>
                <option value="correction_deadline">Deadline Koreksi</option>
                <option value="internal_review">Internal Review</option>
            </select>
            <input name="title" required placeholder="Judul deadline" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="month" name="period" required value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="date" name="due_date" required value="{{ now()->addDays(7)->toDateString() }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" name="reminder_days_before" required min="0" max="60" value="7" placeholder="Reminder H-" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="sla_level" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
            </select>
            <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="planned">Planned</option>
                <option value="in_progress">In Progress</option>
                <option value="waiting_external">Waiting External</option>
                <option value="completed">Completed</option>
                <option value="overdue">Overdue</option>
                <option value="escalated">Escalated</option>
            </select>
            <select name="owner_user_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Owner belum dipilih</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <select name="escalation_user_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Escalation belum dipilih</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <input name="notes" placeholder="Catatan compliance" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 xl:col-span-2">
            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-calendar-check"></i>
                Simpan Deadline
            </button>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Timeline Compliance</h2>
            <p class="text-sm text-gray-500">Monitoring deadline, reminder, PIC, SLA, dan eskalasi pajak payroll.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Company</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Due</th>
                        <th class="px-5 py-3">Reminder</th>
                        <th class="px-5 py-3">Owner</th>
                        <th class="px-5 py-3">SLA</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Checklist</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $event->event_number }}</div>
                                <div class="text-xs text-gray-500">{{ $event->title }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $event->company?->code ?? 'All' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $event->period }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $event->due_date?->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $event->reminder_date?->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $event->owner?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">Esc: {{ $event->escalationUser?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $event->sla_level === 'critical' ? 'bg-red-50 text-red-600' : ($event->sla_level === 'high' ? 'bg-yellow-50 text-yellow-600' : 'bg-blue-50 text-blue-600') }}">{{ ucfirst($event->sla_level) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ str_replace('_', ' ', ucfirst($event->status)) }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-500">{{ implode(' | ', array_slice($event->checklist_snapshot ?? [], 0, 3)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada deadline compliance pajak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
