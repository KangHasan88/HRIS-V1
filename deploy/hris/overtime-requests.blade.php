@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Pengajuan Lembur</h1>
            <p class="mt-1 text-sm text-gray-500">Catat lembur, approval, estimasi nominal, dan handoff ke payroll.</p>
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
                ['label' => 'Total Lembur', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Menunggu', 'value' => $summary['submitted'], 'color' => 'text-yellow-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Payroll', 'value' => $summary['payroll'], 'color' => 'text-gray-900'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Buat Pengajuan</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Lembur',
                        'description' => 'Form ini dipakai HR/admin untuk mencatat lembur, status approval, dan estimasi handoff payroll.',
                        'steps' => [
                            'Pilih karyawan, tanggal, jam mulai, jam selesai, dan tipe lembur.',
                            'Isi rate per jam dan status approval.',
                            'Centang handoff payroll hanya jika lembur sudah approved.',
                        ],
                        'notes' => [
                            'Estimasi = approved hours x hourly rate x multiplier.',
                            'Multiplier awal: regular 1.5, night 1.5, rest day 1.75, holiday 2.0.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Approved lembur bisa ditandai untuk komponen payroll.</p>
            </div>

            <form action="{{ route('attendance.overtime-requests.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Lembur</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Request</label>
                            <input name="request_number" required placeholder="Contoh: OT-202607-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
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
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Lembur</label>
                            <input type="date" name="overtime_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe Lembur</label>
                            <select name="overtime_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="regular_day">Hari kerja</option>
                                <option value="night_shift">Shift malam</option>
                                <option value="rest_day">Hari istirahat</option>
                                <option value="holiday">Hari libur</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai</label>
                            <input type="datetime-local" name="start_at" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Selesai</label>
                            <input type="datetime-local" name="end_at" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Approval & Payroll</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sumber</label>
                            <select name="request_source" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="web_admin">Web Admin</option>
                                <option value="ess_webview">ESS WebView</option>
                                <option value="mobile_webview">Mobile WebView</option>
                            </select>
                        </div>
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
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Rate per Jam</label>
                            <input type="number" min="0" step="100" name="hourly_rate" required value="25000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Komponen Payroll</label>
                            <input name="payroll_component_code" placeholder="Contoh: OT" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="handoff_to_payroll" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span><span class="block font-medium text-gray-800">Handoff ke payroll</span><span class="text-xs text-gray-500">Aktifkan jika lembur approved dan siap masuk komponen payroll.</span></span>
                            </label>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approver Saat Ini</label>
                            <input name="current_approver" placeholder="Contoh: Manager Operasional" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Alasan Lembur</label>
                            <input name="reason" placeholder="Contoh: Closing produksi / urgent maintenance" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Keputusan</label>
                            <textarea name="decision_notes" rows="3" placeholder="Catatan approval, koreksi jam, atau alasan penolakan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Lembur
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Lembur</h2>
                <p class="text-sm text-gray-500">Estimasi payroll dan status approval per request.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Request</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Jam</th>
                            <th class="px-5 py-3">Estimasi</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($requests as $overtime)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4"><div class="font-semibold text-gray-900">{{ $overtime->request_number }}</div><div class="text-xs text-gray-500">{{ $overtime->overtime_type }} · {{ $overtime->request_source }}</div></td>
                                <td class="px-5 py-4"><div class="font-semibold text-gray-900">{{ $overtime->employeeProfile?->full_name }}</div><div class="text-xs text-gray-500">{{ $overtime->employeeProfile?->employee_number }}</div></td>
                                <td class="px-5 py-4"><div class="font-semibold text-gray-900">{{ number_format((float) $overtime->approved_hours ?: (float) $overtime->requested_hours, 2) }} jam</div><div class="text-xs text-gray-500">{{ $overtime->start_at?->format('d M H:i') }} - {{ $overtime->end_at?->format('H:i') }}</div></td>
                                <td class="px-5 py-4"><div class="font-semibold text-gray-900">Rp {{ number_format((float) $overtime->estimated_amount, 0, ',', '.') }}</div><div class="text-xs text-gray-500">{{ $overtime->handoff_to_payroll ? 'Payroll handoff' : 'Belum handoff' }}</div></td>
                                <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $overtime->approval_status === 'approved' ? 'bg-green-50 text-green-700' : ($overtime->approval_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700') }}">{{ ucfirst($overtime->approval_status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada pengajuan lembur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
