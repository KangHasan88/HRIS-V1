@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Aset Karyawan</h1>
            <p class="mt-1 text-sm text-gray-500">Penugasan laptop, seragam, ID card, kendaraan, dan clearance pengembalian.</p>
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
                ['label' => 'Ditugaskan', 'value' => $summary['assigned'], 'color' => 'text-blue-600'],
                ['label' => 'Dikembalikan', 'value' => $summary['returned'], 'color' => 'text-green-600'],
                ['label' => 'Overdue', 'value' => $summary['overdue'], 'color' => 'text-red-600'],
                ['label' => 'Clearance Pending', 'value' => $summary['clearance_pending'], 'color' => 'text-yellow-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.75fr_1.25fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Tugaskan Aset</h2>
                <p class="text-sm text-gray-500">Catat aset yang diberikan ke karyawan.</p>
            </div>
            <form action="{{ route('hris.employee-assets.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ $employee->company?->code ?? '-' }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="asset_code" required placeholder="Kode aset" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="asset_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="laptop">Laptop</option>
                        <option value="phone">Handphone</option>
                        <option value="id_card">ID Card</option>
                        <option value="uniform">Seragam</option>
                        <option value="vehicle">Kendaraan</option>
                        <option value="tool">Tools</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <input name="asset_name" required placeholder="Nama aset" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="serial_number" placeholder="Serial number" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="asset_value" type="number" min="0" placeholder="Nilai aset" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input type="date" name="assigned_at" required title="Tanggal serah" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="due_return_at" title="Target kembali" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <select name="condition_out" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="new">Baru</option>
                    <option value="good">Baik</option>
                    <option value="fair">Cukup</option>
                    <option value="damaged">Rusak</option>
                </select>
                <input name="handover_by" placeholder="Diserahkan oleh" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="requires_clearance" value="1" checked class="rounded border-gray-300 text-blue-600">
                    Wajib clearance saat offboarding
                </label>
                <textarea name="notes" rows="3" placeholder="Catatan aset" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Aset
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Aset</h2>
                <p class="text-sm text-gray-500">Tracking aset aktif, overdue, dan pengembalian.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Aset</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Pengembalian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($assets as $asset)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $asset->asset_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $asset->asset_code }} / {{ strtoupper(str_replace('_', ' ', $asset->asset_type)) }}</div>
                                    <div class="text-xs text-gray-500">{{ $asset->serial_number ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $asset->employeeProfile?->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $asset->employeeProfile?->employee_number }} / {{ $asset->employeeProfile?->company?->code ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    Serah: {{ $asset->assigned_at?->format('d M Y') }}
                                    <div class="text-xs text-gray-500">Target: {{ $asset->due_return_at?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($asset->assignment_status) }}</span>
                                    <div class="mt-1 text-xs text-gray-500">Clearance: {{ $asset->clearance_completed ? 'Selesai' : 'Pending' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($asset->assignment_status === 'assigned')
                                        <form action="{{ route('hris.employee-assets.return', $asset) }}" method="POST" class="space-y-2">
                                            @csrf
                                            <div class="grid gap-2 sm:grid-cols-2">
                                                <input type="date" name="returned_at" required class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                                <select name="condition_in" required class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                                    <option value="good">Baik</option>
                                                    <option value="fair">Cukup</option>
                                                    <option value="damaged">Rusak</option>
                                                    <option value="lost">Hilang</option>
                                                </select>
                                            </div>
                                            <input name="received_by" placeholder="Diterima oleh" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                            <label class="flex items-center gap-2 text-xs text-gray-600">
                                                <input type="checkbox" name="clearance_completed" value="1" class="rounded border-gray-300 text-blue-600">
                                                Clearance selesai
                                            </label>
                                            <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                                                <i class="fas fa-rotate-left"></i>
                                                Catat Kembali
                                            </button>
                                        </form>
                                    @else
                                        <div class="text-sm text-gray-600">{{ $asset->returned_at?->format('d M Y') ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $asset->condition_in ?: '-' }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada aset karyawan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
