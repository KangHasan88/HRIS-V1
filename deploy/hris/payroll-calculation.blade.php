@extends('layouts.sidebar')

@section('title', 'Perhitungan Payroll')

@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Perhitungan Payroll</h1>
            <p class="mt-1 text-sm text-gray-500">Preview gross, potongan, PPh 21, BPJS, dan net pay sebelum slip gaji dipublish.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('payroll.run-wizard') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fas fa-list-check mr-2"></i> Wizard Run
            </a>
            <a href="{{ route('payroll.payslips') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-file-invoice mr-2"></i> Slip Gaji
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Pilih Payroll Run</h2>
                <p class="text-sm text-gray-500">Gunakan run yang sudah dibuat dari wizard, lalu jalankan hitung untuk memperbarui estimasi payroll.</p>
            </div>
            <div class="group relative">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    Halaman ini menghitung preview payroll dari salary assignment approved, adjustment approved/posted, profil BPJS aktif, dan profil PPh 21. Hasilnya belum otomatis menjadi slip gaji; payroll tetap perlu review sebelum publish dan pencairan.
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
            <form method="GET" action="{{ route('payroll.calculation') }}" class="grid gap-3 md:grid-cols-[1fr_auto]">
                <label class="block">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Payroll Run</span>
                    <select name="run_id" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @forelse($runs as $run)
                            <option value="{{ $run->id }}" @selected($selectedRun?->id === $run->id)>
                                {{ $run->run_number }} - {{ $run->period }} - {{ $run->company?->code ?? 'ALL' }} - {{ ucfirst($run->status) }}
                            </option>
                        @empty
                            <option value="">Belum ada payroll run</option>
                        @endforelse
                    </select>
                </label>
                <button class="mt-5 inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-filter mr-2"></i> Tampilkan
                </button>
            </form>

            @if($selectedRun)
                <form method="POST" action="{{ route('payroll.calculation.calculate', $selectedRun) }}">
                    @csrf
                    <button class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 lg:w-auto">
                        <i class="fas fa-calculator mr-2"></i> Hitung Payroll
                    </button>
                </form>
            @endif
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Karyawan', 'value' => $summary['employees'], 'color' => 'text-blue-600', 'sub' => 'Masuk preview'],
                ['label' => 'Gross Pay', 'value' => $money($summary['gross']), 'color' => 'text-gray-900', 'sub' => 'Gaji + tunjangan'],
                ['label' => 'Potongan', 'value' => $money($summary['deduction']), 'color' => 'text-amber-600', 'sub' => 'BPJS + PPh + koreksi'],
                ['label' => 'Net Pay', 'value' => $money($summary['net']), 'color' => 'text-green-600', 'sub' => 'Estimasi dibayarkan'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="mt-5 grid gap-3 border-t border-gray-100 pt-4 md:grid-cols-4">
            <div class="rounded-xl border border-gray-200 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">BPJS Karyawan</div>
                <div class="mt-1 font-semibold text-gray-900">{{ $money($summary['bpjs_employee']) }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">BPJS Perusahaan</div>
                <div class="mt-1 font-semibold text-gray-900">{{ $money($summary['bpjs_employer']) }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Estimasi PPh 21</div>
                <div class="mt-1 font-semibold text-gray-900">{{ $money($summary['pph21']) }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Perlu Cek</div>
                <div class="mt-1 font-semibold {{ $summary['blocked'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $summary['blocked'] }}</div>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Preview Per Karyawan</h2>
            <p class="text-sm text-gray-500">Baris “Perlu cek” harus dibereskan dulu sebelum slip gaji dipublish.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Gross</th>
                        <th class="px-5 py-3">BPJS</th>
                        <th class="px-5 py-3">PPh 21</th>
                        <th class="px-5 py-3">Potongan</th>
                        <th class="px-5 py-3">Net Pay</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($preview as $row)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $row['employee']->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $row['employee']->employee_number }} · {{ $row['employee']->company?->code ?? '-' }} · {{ $row['employee']->department ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $money($row['gross']) }}</div>
                                <div class="text-xs text-gray-500">Base {{ $money($row['base']) }} + allowance {{ $money($row['fixed'] + $row['variable']) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $money($row['bpjs_employee']) }}</div>
                                <div class="text-xs text-gray-500">Perusahaan {{ $money($row['bpjs_employer']) }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $money($row['pph21']) }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-amber-600">{{ $money($row['deduction']) }}</div>
                                <div class="text-xs text-gray-500">Adjustment {{ $money($row['deduction_adjustment']) }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-green-600">{{ $money($row['net']) }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $row['status'] === 'Siap' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ $row['status'] }}</span>
                                @foreach($row['issues'] as $issue)
                                    <div class="mt-1 text-xs text-red-600">{{ $issue }}</div>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada payroll run atau karyawan aktif untuk dihitung.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
