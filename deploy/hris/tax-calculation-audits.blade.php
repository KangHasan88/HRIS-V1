@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Riwayat Perhitungan PPh 21</h1>
            <p class="mt-1 text-sm text-gray-500">Riwayat audit perhitungan pajak payroll: input, snapshot profil, rumus, dan hasil PPh 21.</p>
        </div>
        <a href="{{ route('payroll.tax-profiles') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-file-invoice-dollar"></i>
            Master PPh 21
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
                ['label' => 'Audit Pajak', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Final', 'value' => $summary['final'], 'color' => 'text-green-600'],
                ['label' => 'Draft', 'value' => $summary['draft'], 'color' => 'text-yellow-600'],
                ['label' => 'Total PPh 21', 'value' => 'IDR ' . number_format($summary['total_pph21'], 0, ',', '.'), 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="border-b border-gray-100 pb-4">
            <h2 class="text-lg font-semibold text-gray-900">Buat Simulasi Audit</h2>
            <p class="text-sm text-gray-500">Simulasi ini menyimpan jejak formula sebelum payroll diposting.</p>
        </div>
        <form action="{{ route('payroll.tax-audits.store') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-5">
            @csrf
            <select name="tax_profile_id" required class="xl:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Pilih profil pajak</option>
                @foreach($profiles as $profile)
                    <option value="{{ $profile->id }}">{{ $profile->employee_name }} - {{ $profile->ptkp_status }} - {{ strtoupper($profile->tax_method) }}</option>
                @endforeach
            </select>
            <input type="month" name="period" required value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="gross_income" required type="number" min="0" step="1000" placeholder="Gaji bruto" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="taxable_allowance" type="number" min="0" step="1000" placeholder="Tunjangan kena pajak" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="deduction" type="number" min="0" step="1000" placeholder="Pengurang" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="calculation_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="draft">Draft</option>
                <option value="final">Final</option>
            </select>
            <div class="xl:col-span-3 flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-calculator"></i>
                    Hitung & Simpan Audit
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Audit Terbaru</h2>
            <p class="text-sm text-gray-500">Maksimal 100 riwayat perhitungan PPh 21 terakhir.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Income</th>
                        <th class="px-5 py-3">PTKP / PKP</th>
                        <th class="px-5 py-3">PPh 21</th>
                        <th class="px-5 py-3">Riwayat Hitung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($audits as $audit)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $audit->taxProfile?->employee_name }}</div>
                                <div class="text-xs text-gray-500">{{ $audit->company?->code ?: '-' }} / {{ $audit->branch?->code ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $audit->period }}</div>
                                <span class="mt-1 inline-flex rounded-full {{ $audit->calculation_status === 'final' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }} px-2 py-1 text-xs font-semibold">{{ ucfirst($audit->calculation_status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-500">Gross</div>
                                <div class="font-semibold text-gray-900">IDR {{ number_format($audit->gross_income, 0, ',', '.') }}</div>
                                <div class="mt-1 text-xs text-gray-500">Net IDR {{ number_format($audit->net_income, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-500">PTKP IDR {{ number_format($audit->ptkp_amount, 0, ',', '.') }}</div>
                                <div class="font-semibold text-gray-900">PKP IDR {{ number_format($audit->pkp_amount, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-500">{{ strtoupper($audit->tax_method) }}</div>
                                <div class="font-semibold text-red-600">IDR {{ number_format($audit->pph21_amount, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="max-w-md space-y-2">
                                    @foreach(array_slice($audit->calculation_steps ?? [], 0, 4) as $step)
                                        <div class="rounded-lg bg-gray-50 px-3 py-2">
                                            <div class="font-semibold text-gray-900">{{ $step['label'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $step['formula'] }}: IDR {{ number_format($step['amount'], 0, ',', '.') }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada audit perhitungan PPh 21.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
