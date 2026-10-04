@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Simulasi Pajak Saya</h1>
        <p class="mt-1 text-sm text-gray-500">Lihat scenario PPh 21 yang sudah disetujui payroll untuk ditampilkan ke karyawan.</p>
    </div>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-3">
            @foreach([
                ['label' => 'Scenario', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Total PPh 21', 'value' => 'Rp ' . number_format($summary['pph21'], 0, ',', '.'), 'color' => 'text-red-600'],
                ['label' => 'Estimasi THP', 'value' => 'Rp ' . number_format($summary['thp'], 0, ',', '.'), 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    @if(! $profile)
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-sm text-yellow-800 shadow-sm">
            <div class="font-semibold">Profil pajak belum terhubung.</div>
            <p class="mt-1">Simulasi akan tampil setelah HR/payroll menghubungkan Employee Profile dengan Tax Profile.</p>
        </div>
    @endif

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Scenario Approved</h2>
            <p class="text-sm text-gray-500">Informasi ini bersifat simulasi dan tidak menggantikan slip gaji final.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Scenario</th>
                        <th class="px-5 py-3">Jenis</th>
                        <th class="px-5 py-3">PPh 21</th>
                        <th class="px-5 py-3">THP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($simulations as $simulation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $simulation->scenario_name }}</div>
                                <div class="text-xs text-gray-500">{{ $simulation->simulation_number }} · {{ $simulation->period }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ str_replace('_', ' ', ucfirst($simulation->scenario_type)) }}</td>
                            <td class="px-5 py-4 font-semibold text-red-600">Rp {{ number_format($simulation->pph21_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-semibold text-green-600">Rp {{ number_format($simulation->take_home_pay, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada simulasi pajak yang ditampilkan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
