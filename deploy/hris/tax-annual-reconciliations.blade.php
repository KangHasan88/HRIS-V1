@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">True-up PPh 21 Tahunan</h1>
            <p class="mt-1 text-sm text-gray-500">Rekonsiliasi pajak akhir tahun dan koreksi Desember berdasarkan potongan bulanan final.</p>
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
                ['label' => 'Rekonsiliasi', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Kurang Bayar', 'value' => $summary['underpaid'], 'color' => 'text-red-600'],
                ['label' => 'Lebih Bayar', 'value' => $summary['overpaid'], 'color' => 'text-green-600'],
                ['label' => 'Net True-up', 'value' => 'IDR ' . number_format($summary['net_true_up'], 0, ',', '.'), 'color' => 'text-yellow-600'],
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
            <h2 class="text-lg font-semibold text-gray-900">Hitung True-up</h2>
            <p class="text-sm text-gray-500">Gunakan semua kalkulasi bulanan final pada tahun pajak terpilih.</p>
        </div>
        <form action="{{ route('payroll.tax-annual.reconcile') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-4">
            @csrf
            <select name="tax_profile_id" required class="xl:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Pilih profil pajak</option>
                @foreach($profiles as $profile)
                    <option value="{{ $profile->id }}">{{ $profile->employee_name }} - {{ $profile->ptkp_status }}</option>
                @endforeach
            </select>
            <input type="number" name="tax_year" required value="{{ now()->year }}" min="2020" max="2100" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="calculation_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="draft">Draft</option>
                <option value="final">Final</option>
            </select>
            <div class="xl:col-span-4 flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-scale-balanced"></i>
                    Hitung True-up
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat True-up</h2>
            <p class="text-sm text-gray-500">100 rekonsiliasi PPh 21 tahunan terakhir.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Tahun</th>
                        <th class="px-5 py-3">Neto / PKP</th>
                        <th class="px-5 py-3">Terutang</th>
                        <th class="px-5 py-3">Dipotong</th>
                        <th class="px-5 py-3">True-up</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reconciliations as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $item->taxProfile?->employee_name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->taxProfile?->ptkp_status }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $item->tax_year }}</div>
                                <span class="text-xs text-gray-500">{{ ucfirst($item->calculation_status) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-500">Neto IDR {{ number_format($item->annual_net_income, 0, ',', '.') }}</div>
                                <div class="font-semibold text-gray-900">PKP IDR {{ number_format($item->pkp_amount, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($item->annual_pph21_due, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($item->monthly_pph21_paid, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold {{ $item->true_up_amount > 0 ? 'text-red-600' : ($item->true_up_amount < 0 ? 'text-green-600' : 'text-gray-900') }}">IDR {{ number_format($item->true_up_amount, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">{{ str_replace('_', ' ', $item->true_up_status) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada true-up PPh 21 tahunan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
