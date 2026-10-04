@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Status Kerja</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola siklus probation, kontrak, tetap, magang, dan alumni.</p>
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
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Probation', 'value' => $summary['probation'], 'color' => 'text-yellow-600'],
                ['label' => 'Kontrak', 'value' => $summary['contract'], 'color' => 'text-blue-600'],
                ['label' => 'Tetap', 'value' => $summary['permanent'], 'color' => 'text-green-600'],
                ['label' => 'Alumni', 'value' => $summary['alumni'], 'color' => 'text-gray-600'],
                ['label' => 'Review 30 Hari', 'value' => $summary['due_review'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.85fr_1.15fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Ubah Status</h2>
                <p class="text-sm text-gray-500">Setiap perubahan tersimpan sebagai riwayat dan audit trail.</p>
            </div>
            <form action="{{ route('hris.employee-status.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ ucfirst($employee->employment_status) }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="to_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="probation">Probation</option>
                        <option value="contract">Kontrak</option>
                        <option value="permanent">Tetap</option>
                        <option value="intern">Magang</option>
                        <option value="alumni">Alumni</option>
                    </select>
                    <input type="date" name="effective_date" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input type="date" name="review_date" title="Tanggal review" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="contract_end_date" title="Akhir kontrak" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="reason" placeholder="Alasan perubahan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="approved_by" placeholder="Disetujui oleh" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <textarea name="notes" rows="3" placeholder="Catatan tambahan" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Status
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Karyawan Aktif</h2>
                <p class="text-sm text-gray-500">Ringkasan status terbaru dan tanggal penting.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Kontrak</th>
                            <th class="px-5 py-3">Update Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($employees as $employee)
                            @php($latest = $employee->statusHistories->first())
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $employee->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $employee->employee_number }} / {{ $employee->company?->code ?? '-' }} - {{ $employee->branch?->code ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($employee->employment_status) }}</span>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $employee->contract_end_date?->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-4 text-gray-600">
                                    {{ $latest?->effective_date?->format('d M Y') ?? '-' }}
                                    <div class="text-xs text-gray-500">{{ $latest?->reason ?? 'Belum ada riwayat' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada data karyawan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Status</h2>
            <p class="text-sm text-gray-500">Jejak perubahan status kerja untuk audit HR.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Perubahan</th>
                        <th class="px-5 py-3">Review</th>
                        <th class="px-5 py-3">Approval</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($histories as $history)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-gray-600">{{ $history->effective_date?->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $history->employeeProfile?->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $history->employeeProfile?->employee_number }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ ucfirst($history->from_status ?? '-') }} ke {{ ucfirst($history->to_status) }}<div class="text-xs text-gray-500">{{ $history->reason ?: '-' }}</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ $history->review_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $history->approved_by ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada riwayat status.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
