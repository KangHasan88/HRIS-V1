@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Attendance & Mandays</h1>
            <p class="mt-1 text-sm text-gray-500">Catat kehadiran tenaga outsource, harian, borongan, dan non-employee untuk approval serta handoff invoice/payroll.</p>
        </div>
        <a href="{{ route('external-workforce.workers') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-user-gear"></i> Worker Non-Employee
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Total Record', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Mandays', 'value' => number_format($summary['mandays'], 2, ',', '.'), 'color' => 'text-blue-600'],
                ['label' => 'Ready Handoff', 'value' => $summary['ready_handoff'], 'color' => 'text-amber-600'],
                ['label' => 'Nilai Bruto', 'value' => 'Rp '.number_format($summary['gross_amount'], 0, ',', '.'), 'color' => 'text-gray-900'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Attendance</h2>
                @include('partials.form-help', [
                    'title' => 'Cara pakai Attendance & Mandays',
                    'description' => 'Form ini dipakai HR/site admin untuk mencatat kehadiran worker non-employee. Data yang sudah approved menjadi dasar rekonsiliasi invoice vendor, payroll internal, atau hanya arsip operasional.',
                    'steps' => [
                        'Pilih worker. Vendor, kontrak, perusahaan, cabang, dan cost center akan mengikuti master worker.',
                        'Isi tanggal, status hadir, jam masuk/keluar, mandays, dan overtime jika ada.',
                        'Pilih approval status. Jika langsung approved, isi nama approver/supervisor.',
                        'Handoff otomatis siap jika approved dan payment handling worker adalah vendor invoice atau payroll internal.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Gunakan untuk tenaga harian/outsourcing yang absensinya perlu dihitung menjadi mandays dan nilai bruto.</p>
        </div>

        <form action="{{ route('external-workforce.attendance.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="grid gap-4 xl:grid-cols-3">
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Attendance</h3>
                    <div class="mt-3 grid gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Worker</label>
                            <select name="external_workforce_worker_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih worker non-employee</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->worker_number }} - {{ $worker->full_name }} ({{ str_replace('_', ' ', $worker->payment_handling) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Attendance</label>
                            <input name="attendance_number" required placeholder="Contoh: EXT-ATT-098" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-1">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal</label>
                                <input type="date" name="attendance_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Shift / Area</label>
                                <input name="shift_code" placeholder="Contoh: SHIFT-DAY / Site A" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Jam & Mandays</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Hadir</label>
                            <select name="attendance_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="present">Hadir penuh</option>
                                <option value="half_day">Setengah hari</option>
                                <option value="absent">Tidak hadir</option>
                                <option value="no_show">No show</option>
                                <option value="leave">Izin/cuti</option>
                                <option value="sick">Sakit</option>
                                <option value="holiday">Libur</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Clock In</label>
                            <input type="datetime-local" name="clock_in_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Clock Out</label>
                            <input type="datetime-local" name="clock_out_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mandays</label>
                            <input type="number" name="mandays" value="1" step="0.25" min="0" max="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jam Lembur</label>
                            <input type="number" name="overtime_hours" value="0" step="0.25" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tarif Harian</label>
                            <input type="number" name="daily_rate" value="0" min="0" placeholder="0 = ikut master worker" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tarif Lembur</label>
                            <input type="number" name="overtime_rate" value="0" min="0" placeholder="0 = ikut kontrak" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Approval & Handoff</h3>
                    <div class="mt-3 grid gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                            <select name="approval_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="submitted">Submitted</option>
                                <option value="draft">Draft</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approver / Supervisor</label>
                            <input name="approved_by" placeholder="Nama supervisor jika sudah approved" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber Data</label>
                            <select name="source_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="manual">Manual input</option>
                                <option value="agent">Agent attendance</option>
                                <option value="csv">CSV import</option>
                                <option value="vendor_report">Laporan vendor</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bukti / Referensi</label>
                            <input name="evidence_reference" placeholder="File, URL, nomor timesheet, atau laporan vendor" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                            <textarea name="notes" rows="3" placeholder="Catatan koreksi jam, mandays, atau arahan invoice/payroll." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-calendar-check"></i> Simpan Attendance
                </button>
            </div>
        </form>
    </section>

    <section class="panel rounded-2xl">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Daftar Attendance</h2>
                <p class="text-sm text-gray-500">Data approved menjadi dasar rekap mandays dan handoff invoice/payroll.</p>
            </div>
            <form method="GET" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="approval_status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua approval</option>
                    @foreach(['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['approval_status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="handoff_status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua handoff</option>
                    @foreach(['pending' => 'Pending', 'ready' => 'Ready', 'sent' => 'Sent', 'not_required' => 'Tidak perlu'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['handoff_status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Attendance</th>
                        <th class="px-5 py-3">Worker</th>
                        <th class="px-5 py-3">Mandays</th>
                        <th class="px-5 py-3">Nilai</th>
                        <th class="px-5 py-3">Approval</th>
                        <th class="px-5 py-3">Handoff</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attendances as $attendance)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs text-gray-500">{{ $attendance->attendance_number }}</div>
                                <div class="font-semibold text-gray-900">{{ $attendance->attendance_date?->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $attendance->shift_code ?: '-' }} / {{ str_replace('_', ' ', $attendance->source_type) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $attendance->worker?->full_name ?: '-' }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $attendance->worker?->worker_number ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $attendance->vendor?->vendor_name ?: 'Tanpa vendor' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div>{{ str_replace('_', ' ', $attendance->attendance_status) }}</div>
                                <div class="text-xs text-gray-500">{{ number_format((float) $attendance->mandays, 2, ',', '.') }} mandays</div>
                                <div class="text-xs text-gray-500">{{ number_format((float) $attendance->overtime_hours, 2, ',', '.') }} jam lembur</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div class="font-semibold text-gray-900">Rp {{ number_format((float) $attendance->gross_amount, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Harian Rp {{ number_format((float) $attendance->daily_rate, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Lembur Rp {{ number_format((float) $attendance->overtime_rate, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full {{ $attendance->approval_status === 'approved' ? 'bg-green-50 text-green-700' : ($attendance->approval_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }} px-2 py-1 text-xs font-semibold">
                                    {{ ucfirst($attendance->approval_status) }}
                                </span>
                                <div class="mt-2 text-xs text-gray-500">{{ $attendance->approved_by ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-gray-700">{{ str_replace('_', ' ', $attendance->handoff_target) }}</div>
                                <span class="mt-2 inline-flex rounded-full {{ $attendance->handoff_status === 'ready' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700' }} px-2 py-1 text-xs font-semibold">
                                    {{ str_replace('_', ' ', ucfirst($attendance->handoff_status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if(!in_array($attendance->approval_status, ['approved', 'rejected'], true))
                                    <form action="{{ route('external-workforce.attendance.review', $attendance) }}" method="POST" class="flex flex-col gap-2">
                                        @csrf
                                        <input type="hidden" name="approved_by" value="{{ auth()->user()->name }}">
                                        <button name="decision" value="approved" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Approve</button>
                                        <button name="decision" value="rejected" class="rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">Reject</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Reviewed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500">Belum ada attendance non-employee untuk filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
