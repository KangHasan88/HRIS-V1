@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Offboarding</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola resign, clearance, exit interview, handover, dan final settlement.</p>
        </div>
        <a href="{{ route('hris.employees') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-users"></i>
            Data Karyawan
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
                ['label' => 'Initiated', 'value' => $summary['initiated'], 'color' => 'text-blue-600'],
                ['label' => 'Clearance', 'value' => $summary['clearance'], 'color' => 'text-yellow-600'],
                ['label' => 'Settlement', 'value' => $summary['settlement'], 'color' => 'text-indigo-600'],
                ['label' => 'Closed', 'value' => $summary['closed'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.8fr_1.2fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Buat Case Offboarding</h2>
                <p class="text-sm text-gray-500">Catat proses keluar karyawan beserta checklist clearance.</p>
            </div>
            <form action="{{ route('hris.employee-offboarding.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ $employee->position ?? '-' }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="case_number" required placeholder="Nomor case" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="reason_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="resign">Resign</option>
                        <option value="termination">Terminasi</option>
                        <option value="retirement">Pensiun</option>
                        <option value="end_contract">Kontrak berakhir</option>
                        <option value="mutual_agreement">Kesepakatan bersama</option>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <input type="date" name="notice_date" title="Tanggal notice" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="last_working_date" required title="Hari kerja terakhir" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="exit_interview_date" title="Exit interview" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="initiated">Initiated</option>
                        <option value="clearance">Clearance</option>
                        <option value="settlement">Settlement</option>
                        <option value="closed">Closed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select name="final_settlement_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="pending">Settlement pending</option>
                        <option value="calculated">Calculated</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <input name="handled_by" placeholder="Ditangani oleh" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="approved_by" placeholder="Disetujui oleh" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="number" name="final_settlement_amount" min="0" placeholder="Nilai settlement" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <textarea name="reason_note" rows="2" placeholder="Catatan alasan keluar" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <textarea name="exit_interview_summary" rows="2" placeholder="Ringkasan exit interview" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <textarea name="handover_note" rows="2" placeholder="Catatan handover" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <input name="clearance_items[]" required value="Pengembalian aset kerja" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="clearance_items[]" required value="Penutupan akses sistem dan akun" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="clearance_items[]" required value="Final settlement payroll" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Offboarding
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Offboarding</h2>
                <p class="text-sm text-gray-500">Tracking case, settlement, dan clearance item.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Case</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Settlement</th>
                            <th class="px-5 py-3">Clearance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($cases as $case)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $case->case_number }}</div>
                                    <div class="text-xs text-gray-500">{{ strtoupper(str_replace('_', ' ', $case->reason_type)) }}</div>
                                    <span class="mt-2 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($case->status) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $case->employeeProfile?->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $case->employeeProfile?->employee_number }} / {{ $case->employeeProfile?->branch?->code ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    Notice: {{ $case->notice_date?->format('d M Y') ?? '-' }}
                                    <div class="text-xs text-gray-500">Last day: {{ $case->last_working_date?->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">Interview: {{ $case->exit_interview_date?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    Rp {{ number_format((float) $case->final_settlement_amount, 0, ',', '.') }}
                                    <div class="text-xs text-gray-500">{{ ucfirst($case->final_settlement_status) }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="space-y-2">
                                        @foreach($case->clearanceItems as $item)
                                            <form action="{{ route('hris.employee-offboarding.clearance.update', $item) }}" method="POST" class="rounded-lg border border-gray-100 p-2">
                                                @csrf
                                                <div class="font-medium text-gray-900">{{ $item->item_name }}</div>
                                                <div class="mb-2 text-xs text-gray-500">{{ $item->owner_role ?: '-' }} / due {{ $item->due_date?->format('d M Y') ?? '-' }}</div>
                                                <div class="flex items-center gap-2">
                                                    <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                                        @foreach(['open' => 'Open', 'in_progress' => 'Progress', 'completed' => 'Selesai', 'waived' => 'Waived', 'blocked' => 'Blocked'] as $value => $label)
                                                            <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="date" name="completed_at" value="{{ $item->completed_at?->format('Y-m-d') }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                                    <button class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Update</button>
                                                </div>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada case offboarding.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
