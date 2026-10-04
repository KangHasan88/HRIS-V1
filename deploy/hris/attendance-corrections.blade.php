@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Koreksi Absensi</h1>
        <p class="mt-1 text-sm text-gray-500">Workflow missing punch, salah jam, salah tipe event, dan koreksi absensi dengan approval.</p>
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
                ['label' => 'Total Koreksi', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Menunggu', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Generate Event', 'value' => $summary['generated'], 'color' => 'text-gray-900'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.95fr_1.05fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Koreksi</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Koreksi Absensi',
                        'description' => 'Form ini dipakai untuk mengajukan missing punch atau koreksi jam absensi dengan approval dan audit trail.',
                        'steps' => [
                            'Pilih karyawan, tanggal absensi, tipe koreksi, dan event yang diminta.',
                            'Link ke clock event lama jika koreksi berasal dari event existing.',
                            'Jika approved, centang generate event agar sistem membuat event absensi koreksi.',
                        ],
                        'notes' => [
                            'Generated event diberi nomor CORR-{nomor koreksi}.',
                            'ESS/mobile dapat memakai source ess_webview/mobile_webview untuk request karyawan.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Koreksi approved dapat membuat event absensi baru.</p>
            </div>
            <form action="{{ route('attendance.corrections.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Koreksi</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Koreksi</label>
                            <input name="correction_number" required placeholder="Contoh: COR-202607-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
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
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Absensi</label>
                            <input type="date" name="attendance_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Koreksi</label>
                            <select name="correction_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="missing_clock_in">Missing clock-in</option>
                                <option value="missing_clock_out">Missing clock-out</option>
                                <option value="wrong_time">Salah jam</option>
                                <option value="wrong_type">Salah tipe event</option>
                                <option value="duplicate_punch">Duplicate punch</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Link Clock Event Lama</label>
                            <select name="attendance_clock_event_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Opsional jika missing punch baru</option>
                                @foreach($clockEvents as $event)
                                    <option value="{{ $event->id }}">{{ $event->event_number }} - {{ $event->employeeProfile?->employee_number }} - {{ $event->event_at?->format('d M H:i') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Event Diminta</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jam Original</label>
                            <input type="datetime-local" name="original_event_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jam yang Diminta</label>
                            <input type="datetime-local" name="requested_event_at" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Event Diminta</label>
                            <select name="requested_event_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="clock_in">Clock-in</option>
                                <option value="clock_out">Clock-out</option>
                                <option value="break_start">Mulai istirahat</option>
                                <option value="break_end">Selesai istirahat</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber Request</label>
                            <select name="request_source" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="web_admin">Web Admin</option>
                                <option value="ess_webview">ESS WebView</option>
                                <option value="mobile_webview">Mobile WebView</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Approval</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Approval</label>
                            <select name="approval_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="submitted">Submitted</option>
                                <option value="draft">Draft</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approver Saat Ini</label>
                            <input name="current_approver" placeholder="Contoh: Manager Operasional" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="creates_clock_event" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span><span class="block font-medium text-gray-800">Generate event absensi jika approved</span><span class="text-xs text-gray-500">Sistem membuat event baru dari jam dan tipe yang diminta.</span></span>
                            </label>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Referensi Lampiran</label>
                            <input name="attachment_reference" placeholder="Nomor dokumen, URL, atau file reference" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan</label>
                            <textarea name="reason" rows="3" placeholder="Alasan koreksi atau missing punch." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Keputusan</label>
                            <textarea name="decision_notes" rows="3" placeholder="Catatan approval atau alasan penolakan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Koreksi
                </button>
            </form>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Koreksi</h2>
                <p class="text-sm text-gray-500">Riwayat approval dan generated event dari koreksi absensi.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500"><th class="px-5 py-3">Koreksi</th><th class="px-5 py-3">Karyawan</th><th class="px-5 py-3">Event</th><th class="px-5 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($corrections as $correction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4"><div class="font-semibold text-gray-900">{{ $correction->correction_number }}</div><div class="text-xs text-gray-500">{{ $correction->correction_type }} / {{ $correction->request_source }}</div></td>
                                <td class="px-5 py-4"><div class="font-semibold text-gray-900">{{ $correction->employeeProfile?->full_name }}</div><div class="text-xs text-gray-500">{{ $correction->employeeProfile?->employee_number }}</div></td>
                                <td class="px-5 py-4"><div class="font-semibold text-gray-900">{{ $correction->requested_event_type }} {{ $correction->requested_event_at?->format('d M H:i') }}</div><div class="text-xs text-gray-500">{{ $correction->generated_event_number ?: 'Belum generate event' }}</div></td>
                                <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $correction->approval_status === 'approved' ? 'bg-green-50 text-green-700' : ($correction->approval_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700') }}">{{ ucfirst($correction->approval_status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada koreksi absensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
