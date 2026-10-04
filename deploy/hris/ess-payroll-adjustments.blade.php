@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Penyesuaian Payroll Saya</h1>
        <p class="mt-1 text-sm text-gray-500">Lihat backpay, retroaktif, atau koreksi payroll yang sudah approved/posted.</p>
    </div>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-3">
            @foreach([
                ['label' => 'Total Item', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Posted', 'value' => $summary['posted'], 'color' => 'text-green-600'],
                ['label' => 'Net Impact', 'value' => 'Rp ' . number_format($summary['net'], 0, ',', '.'), 'color' => $summary['net'] < 0 ? 'text-red-600' : 'text-gray-900'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    @if(! $employee)
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-sm text-yellow-800 shadow-sm">
            <div class="font-semibold">Akun belum terhubung ke profil karyawan.</div>
            <p class="mt-1">Data adjustment payroll akan tampil setelah akun terhubung ke Employee Profile.</p>
        </div>
    @endif

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Adjustment</h2>
            <p class="text-sm text-gray-500">Detail ini membantu karyawan memahami perubahan payroll di periode berjalan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Adjustment</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Impact</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($adjustments as $adjustment)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $adjustment->component_name }}</div>
                                <div class="text-xs text-gray-500">{{ $adjustment->adjustment_number }} · {{ str_replace('_', ' ', $adjustment->adjustment_type) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900">{{ $adjustment->period_from }} - {{ $adjustment->period_to }}</div>
                                <div class="text-xs text-gray-500">Posting {{ $adjustment->posting_period }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold {{ ($adjustment->impact_snapshot['net_impact'] ?? 0) < 0 ? 'text-red-600' : 'text-gray-900' }}">Rp {{ number_format($adjustment->impact_snapshot['net_impact'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ ucfirst($adjustment->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada adjustment payroll.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
