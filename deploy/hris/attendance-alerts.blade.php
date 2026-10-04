@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Alert Absensi</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau keterlambatan, absen, fatigue, missing punch, dan kepatuhan waktu kerja.</p>
        </div>
        <a href="{{ route('attendance.corrections') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-pen-to-square"></i> Koreksi Absensi
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
                ['label' => 'Total Alert', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Perlu Follow-up', 'value' => $summary['open'], 'color' => 'text-amber-600'],
                ['label' => 'High/Critical', 'value' => $summary['critical'], 'color' => 'text-red-600'],
                ['label' => 'Resolved', 'value' => $summary['resolved'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Alert</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Alert Absensi',
                        'description' => 'Form ini dipakai HR untuk mencatat dan menindaklanjuti temuan absensi yang perlu review.',
                        'steps' => [
                            'Pilih karyawan, tanggal alert, tipe alert, severity, dan status follow-up.',
                            'Link ke clock event atau roster shift jika alert berasal dari data absensi tertentu.',
                            'Isi metrik seperti menit terlambat, jam kerja, atau fatigue score sesuai tipe alert.',
                            'Tentukan owner dan due date agar tindak lanjut tidak menggantung.',
                        ],
                        'notes' => [
                            'Ceklist resolved/ignored akan menyimpan waktu penyelesaian dan nama user yang menyelesaikan.',
                            'Alert ini menjadi dasar review manager, koreksi absensi, dan audit kepatuhan.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Gunakan untuk review harian sebelum payroll dan compliance ditutup.</p>
            </div>

            <form action="{{ route('attendance.alerts.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Alert</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Alert</label>
                            <input name="alert_number" required placeholder="Contoh: ALT-202607-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Alert</label>
                            <input type="date" name="alert_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Alert</label>
                            <select name="alert_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="late_arrival">Terlambat</option>
                                <option value="absence">Tidak hadir</option>
                                <option value="fatigue">Fatigue / jam kerja panjang</option>
                                <option value="early_leave">Pulang cepat</option>
                                <option value="missing_punch">Missing punch</option>
                                <option value="compliance">Kepatuhan waktu kerja</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Sumber Data</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Clock Event Terkait</label>
                            <select name="attendance_clock_event_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Opsional</option>
                                @foreach($clockEvents as $event)
                                    <option value="{{ $event->id }}">{{ $event->event_number }} - {{ $event->employeeProfile?->employee_number }} - {{ $event->event_at?->format('d M H:i') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Roster Shift Terkait</label>
                            <select name="attendance_shift_assignment_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Opsional</option>
                                @foreach($shiftAssignments as $assignment)
                                    <option value="{{ $assignment->id }}">{{ $assignment->shiftPattern?->code }} - {{ $assignment->employeeProfile?->full_name ?: ucfirst($assignment->assignment_scope) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Severity & Metrik</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Severity</label>
                            <select name="severity" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Follow-up</label>
                            <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="open">Open</option>
                                <option value="in_review">In review</option>
                                <option value="escalated">Escalated</option>
                                <option value="resolved">Resolved</option>
                                <option value="ignored">Ignored</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Menit Terlambat</label>
                            <input type="number" min="0" max="1440" name="late_minutes" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jam Kerja Aktual</label>
                            <input type="number" min="0" max="48" step="0.25" name="worked_hours" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Fatigue Score (0-100)</label>
                            <input type="number" min="0" max="100" step="0.1" name="fatigue_score" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Rule Kepatuhan</label>
                            <input name="compliance_rule" placeholder="Contoh: Maks. 12 jam kerja/hari" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Tindak Lanjut</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Owner Follow-up</label>
                            <input name="owner_name" placeholder="Contoh: HR Site / Manager Operasional" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Due Follow-up</label>
                            <input type="date" name="due_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan Trigger</label>
                            <textarea name="trigger_reason" rows="3" placeholder="Kenapa alert ini muncul." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Follow-up</label>
                            <textarea name="follow_up_notes" rows="3" placeholder="Aksi HR/manager atau hasil klarifikasi." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-bell"></i> Simpan Alert
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Alert</h2>
                <p class="text-sm text-gray-500">Antrian follow-up alert absensi dan kepatuhan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Alert</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Metrik</th>
                            <th class="px-5 py-3">Follow-up</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($alerts as $alert)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $alert->alert_number }}</div>
                                    <div class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $alert->alert_type)) }} / {{ $alert->alert_date?->format('d M Y') }}</div>
                                    <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ in_array($alert->severity, ['high', 'critical'], true) ? 'bg-red-50 text-red-600' : ($alert->severity === 'medium' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600') }}">{{ ucfirst($alert->severity) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $alert->employeeProfile?->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $alert->employeeProfile?->employee_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $alert->clockEvent?->event_number ?: 'Tidak link clock event' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    <div>Telat: <span class="font-semibold text-gray-900">{{ $alert->late_minutes }}</span> menit</div>
                                    <div>Jam kerja: <span class="font-semibold text-gray-900">{{ $alert->worked_hours }}</span></div>
                                    <div>Fatigue: <span class="font-semibold text-gray-900">{{ $alert->fatigue_score }}</span></div>
                                    <div class="text-xs text-gray-500">{{ $alert->compliance_rule ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ in_array($alert->status, ['resolved', 'ignored'], true) ? 'bg-green-50 text-green-600' : ($alert->status === 'escalated' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600') }} px-3 py-1 text-xs font-semibold">{{ ucwords(str_replace('_', ' ', $alert->status)) }}</span>
                                    <div class="mt-2 text-xs text-gray-500">Owner: {{ $alert->owner_name ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">Due: {{ $alert->due_date?->format('d M Y') ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada alert absensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
