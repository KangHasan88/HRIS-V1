@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Disiplin & Coaching</h1>
            <p class="mt-1 text-sm text-gray-500">Riwayat coaching, surat peringatan, investigasi, corrective action, dan follow-up.</p>
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
                ['label' => 'Open', 'value' => $summary['open'], 'color' => 'text-blue-600'],
                ['label' => 'Monitoring', 'value' => $summary['monitoring'], 'color' => 'text-yellow-600'],
                ['label' => 'Closed', 'value' => $summary['closed'], 'color' => 'text-green-600'],
                ['label' => 'High Risk', 'value' => $summary['high_risk'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.78fr_1.22fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambah Case</h2>
                <p class="text-sm text-gray-500">Catat case dengan action plan dan PIC follow-up.</p>
            </div>
            <form action="{{ route('hris.employee-disciplinary-records.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ $employee->position ?? '-' }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="case_number" required placeholder="Nomor case" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="case_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="coaching">Coaching</option>
                        <option value="verbal_warning">Teguran lisan</option>
                        <option value="sp1">SP 1</option>
                        <option value="sp2">SP 2</option>
                        <option value="sp3">SP 3</option>
                        <option value="investigation">Investigasi</option>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="risk_level" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                    <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="open">Open</option>
                        <option value="monitoring">Monitoring</option>
                        <option value="closed">Closed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input type="date" name="incident_date" required title="Tanggal kejadian" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="issued_date" title="Tanggal diterbitkan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input type="date" name="valid_until" title="Berlaku sampai" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="follow_up_date" title="Tanggal follow-up" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <input name="reported_by" placeholder="Dilaporkan oleh" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="handled_by" placeholder="Ditangani oleh" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="approved_by" placeholder="Disetujui oleh" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <textarea name="summary" rows="3" required placeholder="Ringkasan case" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <textarea name="corrective_action" rows="3" placeholder="Corrective action" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <textarea name="employee_response" rows="2" placeholder="Tanggapan karyawan" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="follow_up_result" placeholder="Hasil follow-up" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="attachment_reference" placeholder="Referensi dokumen/lampiran" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Case
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Case</h2>
                <p class="text-sm text-gray-500">Monitoring status case, masa berlaku, dan follow-up.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Case</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Ringkasan</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($records as $record)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $record->case_number }}</div>
                                    <div class="text-xs text-gray-500">{{ strtoupper(str_replace('_', ' ', $record->case_type)) }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $record->attachment_reference ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $record->employeeProfile?->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $record->employeeProfile?->employee_number }} / {{ $record->employeeProfile?->branch?->code ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="max-w-md text-gray-700">{{ $record->summary }}</div>
                                    <div class="mt-1 text-xs text-gray-500">Action: {{ $record->corrective_action ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    Kejadian: {{ $record->incident_date?->format('d M Y') }}
                                    <div class="text-xs text-gray-500">Follow-up: {{ $record->follow_up_date?->format('d M Y') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">Valid: {{ $record->valid_until?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($record->status) }}</span>
                                    <div class="mt-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ ucfirst($record->risk_level) }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $record->handled_by ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada riwayat disiplin/coaching.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
