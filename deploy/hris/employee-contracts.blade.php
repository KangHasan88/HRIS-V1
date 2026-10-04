@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Kontrak Kerja</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola PKWT, PKWTT, perpanjangan, reminder, dan referensi dokumen.</p>
        </div>
        <a href="{{ route('hris.employee-status') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-id-badge"></i>
            Status Kerja
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'PKWT', 'value' => $summary['pkwt'], 'color' => 'text-blue-600'],
                ['label' => 'PKWTT', 'value' => $summary['pkwtt'], 'color' => 'text-purple-600'],
                ['label' => 'Perpanjangan', 'value' => $summary['renewal'], 'color' => 'text-indigo-600'],
                ['label' => 'Reminder 30 Hari', 'value' => $summary['due_reminder'], 'color' => 'text-red-600'],
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
                <h2 class="text-lg font-semibold text-gray-900">Tambah Kontrak</h2>
                <p class="text-sm text-gray-500">Gunakan kontrak induk saat membuat perpanjangan.</p>
            </div>
            <form action="{{ route('hris.employee-contracts.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ $employee->company?->code ?? '-' }}</option>
                    @endforeach
                </select>
                <select name="parent_contract_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Kontrak induk opsional</option>
                    @foreach($contractOptions as $contract)
                        <option value="{{ $contract->id }}">{{ $contract->contract_number }} - {{ $contract->employeeProfile?->full_name }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="contract_number" required placeholder="Nomor kontrak" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="contract_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="pkwt">PKWT</option>
                        <option value="pkwtt">PKWTT</option>
                        <option value="internship">Magang</option>
                        <option value="freelance">Freelance</option>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input type="date" name="start_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="end_date" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input type="date" name="signed_at" title="Tanggal tanda tangan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="reminder_date" title="Tanggal reminder" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="draft">Draft</option>
                    <option value="active">Aktif</option>
                    <option value="expiring">Akan Berakhir</option>
                    <option value="ended">Berakhir</option>
                    <option value="terminated">Terminasi</option>
                </select>
                <input name="document_reference" placeholder="Referensi dokumen / nomor file" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="approved_by" placeholder="Disetujui oleh" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <textarea name="notes" rows="3" placeholder="Catatan kontrak" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Kontrak
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Kontrak</h2>
                <p class="text-sm text-gray-500">Tracking kontrak aktif, reminder, dan perpanjangan.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Kontrak</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3">Reminder</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($contracts as $contract)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $contract->contract_number }}</div>
                                    <div class="text-xs text-gray-500">{{ strtoupper($contract->contract_type) }}{{ $contract->renewal_sequence ? ' / Renewal ' . $contract->renewal_sequence : '' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $contract->employeeProfile?->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $contract->employeeProfile?->employee_number }} / {{ $contract->employeeProfile?->company?->code ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $contract->start_date?->format('d M Y') }} - {{ $contract->end_date?->format('d M Y') ?? 'Tidak terbatas' }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $contract->reminder_date?->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($contract->status) }}</span>
                                    <div class="mt-1 text-xs text-gray-500">{{ $contract->document_reference ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada kontrak kerja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
