@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Penghasilan Tidak Teratur</h1>
            <p class="mt-1 text-sm text-gray-500">Hitung pajak THR, bonus, komisi, tunjangan tidak teratur, dan pajak final.</p>
        </div>
        <a href="{{ route('payroll.tax-monthly') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-percent"></i>
            Hitung Bulanan
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
                ['label' => 'Transaksi', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Final', 'value' => $summary['final'], 'color' => 'text-green-600'],
                ['label' => 'Total Bruto', 'value' => 'IDR ' . number_format($summary['total_gross'], 0, ',', '.'), 'color' => 'text-yellow-600'],
                ['label' => 'Total Pajak', 'value' => 'IDR ' . number_format($summary['total_tax'], 0, ',', '.'), 'color' => 'text-red-600'],
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
            <h2 class="text-lg font-semibold text-gray-900">Hitung Penghasilan Tidak Teratur</h2>
            <p class="text-sm text-gray-500">Rate diinput dari aturan/komponen payroll yang berlaku untuk tipe penghasilan tersebut.</p>
        </div>
        <form action="{{ route('payroll.tax-irregular.store') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-5">
            @csrf
            <select name="tax_profile_id" required class="xl:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Pilih profil pajak</option>
                @foreach($profiles as $profile)
                    <option value="{{ $profile->id }}">{{ $profile->employee_name }} - {{ $profile->ptkp_status }}</option>
                @endforeach
            </select>
            <input type="month" name="period" required value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="income_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                @foreach($incomeTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select name="tax_method" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                @foreach($taxMethods as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <input name="gross_amount" required type="number" min="0" step="1000" placeholder="Nilai bruto" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="tax_rate" required type="number" min="0" max="1" step="0.000001" placeholder="Rate, contoh 0.05" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="calculation_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="draft">Draft</option>
                <option value="final">Final</option>
            </select>
            <div class="xl:col-span-2 flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-calculator"></i>
                    Hitung
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Penghasilan Tidak Teratur</h2>
            <p class="text-sm text-gray-500">100 kalkulasi THR/bonus/penghasilan final terakhir.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Jenis</th>
                        <th class="px-5 py-3">Bruto</th>
                        <th class="px-5 py-3">Pajak</th>
                        <th class="px-5 py-3">Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $item->taxProfile?->employee_name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->taxProfile?->ptkp_status }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $item->period }}</div>
                                <span class="text-xs text-gray-500">{{ ucfirst($item->calculation_status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $incomeTypes[$item->income_type] ?? $item->income_type }}</div>
                                <div class="text-xs text-gray-500">{{ $taxMethods[$item->tax_method] ?? $item->tax_method }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($item->gross_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-red-600">IDR {{ number_format($item->pph21_amount, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">{{ number_format($item->tax_rate * 100, 3, ',', '.') }}%</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-900">IDR {{ number_format($item->net_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada kalkulasi penghasilan tidak teratur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
