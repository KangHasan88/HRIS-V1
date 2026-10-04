@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Regression Test Pajak</h1>
            <p class="mt-1 text-sm text-gray-500">Test case PPh 21 Indonesia, variasi payroll tax, dan readiness integrasi Coretax.</p>
        </div>
        <a href="{{ route('payroll.tax-simulations') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-flask"></i>
            Sandbox Pajak
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
                ['label' => 'Case', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Passed', 'value' => $summary['passed'], 'color' => 'text-green-600'],
                ['label' => 'Failed', 'value' => $summary['failed'], 'color' => 'text-red-600'],
                ['label' => 'Coretax Valid', 'value' => $summary['coretax_ready'], 'color' => 'text-blue-600'],
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
            <h2 class="text-lg font-semibold text-gray-900">Run Test Case</h2>
            <p class="text-sm text-gray-500">Simpan expected result, actual result, variance, dan status schema Coretax.</p>
        </div>
        <form action="{{ route('payroll.tax-regression-cases.store') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-4">
            @csrf
            <select name="case_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="pph21_monthly">PPh 21 Bulanan</option>
                <option value="pph21_annual">PPh 21 Tahunan</option>
                <option value="irregular_income">Penghasilan Tidak Teratur</option>
                <option value="non_permanent_worker">Non Permanen</option>
                <option value="coretax_template">Template Coretax</option>
            </select>
            <input name="case_name" required placeholder="Nama test case" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="month" name="period" required value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="coretax_schema_version" placeholder="coretax-v1.0" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.01" name="gross_income" required placeholder="Bruto" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.01" name="taxable_allowance" required value="0" placeholder="Tunjangan kena pajak" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.01" name="deduction" required value="0" placeholder="Potongan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.00001" name="ter_rate" required value="0.005" placeholder="Tarif TER" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.01" name="expected_pph21_amount" required placeholder="Expected PPh 21" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="notes" placeholder="Catatan scenario" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 xl:col-span-2">
            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-vial-circle-check"></i>
                Run Case
            </button>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Hasil Regression</h2>
            <p class="text-sm text-gray-500">Riwayat test case, variance, dan status validasi Coretax.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Case</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Expected</th>
                        <th class="px-5 py-3">Actual</th>
                        <th class="px-5 py-3">Variance</th>
                        <th class="px-5 py-3">Coretax</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cases as $case)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $case->case_number }}</div>
                                <div class="text-xs text-gray-500">{{ $case->case_name }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $case->period }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ str_replace('_', ' ', ucfirst($case->case_type)) }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($case->expected_pph21_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($case->actual_pph21_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-semibold {{ abs($case->variance_amount) < 0.01 ? 'text-green-600' : 'text-red-600' }}">IDR {{ number_format($case->variance_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $case->coretax_schema_version ?? '-' }}<div class="text-xs text-gray-500">{{ str_replace('_', ' ', $case->coretax_validation_status) }}</div></td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $case->run_status === 'passed' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">{{ ucfirst($case->run_status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada regression case.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
