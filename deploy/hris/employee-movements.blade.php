@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Mutasi Karyawan</h1>
            <p class="mt-1 text-sm text-gray-500">Workflow mutasi, promosi, demosi, transfer, approval, dan effective date.</p>
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
                ['label' => 'Menunggu Approval', 'value' => $summary['requested'], 'color' => 'text-yellow-600'],
                ['label' => 'Disetujui', 'value' => $summary['approved'], 'color' => 'text-blue-600'],
                ['label' => 'Efektif', 'value' => $summary['effective'], 'color' => 'text-green-600'],
                ['label' => 'Transfer', 'value' => $summary['transfer'], 'color' => 'text-indigo-600'],
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
                <h2 class="text-lg font-semibold text-gray-900">Ajukan Movement</h2>
                <p class="text-sm text-gray-500">Catat perubahan organisasi dan jabatan dengan snapshot sebelum/sesudah.</p>
            </div>
            <form action="{{ route('hris.employee-movements.store') }}" method="POST" class="mt-5 space-y-3">
                @csrf
                <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ $employee->position ?? '-' }}</option>
                    @endforeach
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="movement_number" required placeholder="Nomor movement" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <select name="movement_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="mutation">Mutasi</option>
                        <option value="promotion">Promosi</option>
                        <option value="demotion">Demosi</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input type="date" name="request_date" title="Tanggal request" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input type="date" name="effective_date" required title="Tanggal efektif" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="requested">Menunggu approval</option>
                    <option value="approved">Disetujui</option>
                    <option value="effective">Efektif</option>
                    <option value="draft">Draft</option>
                    <option value="rejected">Ditolak</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="to_company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Perusahaan target</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select name="to_branch_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Cabang/site target</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <input name="to_position" placeholder="Jabatan target" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <div class="grid gap-3 sm:grid-cols-3">
                    <input name="to_department" placeholder="Departemen target" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="to_grade" placeholder="Grade target" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="to_cost_center" placeholder="Cost center target" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <textarea name="reason" rows="3" placeholder="Alasan movement" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="approved_by" placeholder="Disetujui oleh" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="approval_notes" placeholder="Catatan approval" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <textarea name="notes" rows="2" placeholder="Catatan tambahan" class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Movement
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Movement</h2>
                <p class="text-sm text-gray-500">Monitoring mutasi dan approval berdasarkan tanggal efektif.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Movement</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Perubahan</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($movements as $movement)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $movement->movement_number }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($movement->movement_type) }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $movement->employeeProfile?->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->employeeProfile?->employee_number }} / {{ $movement->employeeProfile?->company?->code ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-gray-700">{{ $movement->from_position ?: '-' }} <i class="fas fa-arrow-right mx-1 text-gray-400"></i> {{ $movement->to_position ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->fromBranch?->code ?? '-' }} ke {{ $movement->toBranch?->code ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement->from_grade ?: '-' }} / {{ $movement->from_cost_center ?: '-' }} ke {{ $movement->to_grade ?: '-' }} / {{ $movement->to_cost_center ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    Request: {{ $movement->request_date?->format('d M Y') ?? '-' }}
                                    <div class="text-xs text-gray-500">Efektif: {{ $movement->effective_date?->format('d M Y') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($movement->status) }}</span>
                                    <div class="mt-1 text-xs text-gray-500">{{ $movement->approved_by ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada movement karyawan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
