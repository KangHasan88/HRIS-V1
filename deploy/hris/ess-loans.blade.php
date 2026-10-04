@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Pinjaman Saya</h1>
        <p class="mt-1 text-sm text-gray-500">Lihat saldo pinjaman, potongan koperasi, dan jadwal cicilan payroll.</p>
    </div>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Total Pinjaman', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Saldo', 'value' => 'Rp ' . number_format($summary['outstanding'], 0, ',', '.'), 'color' => 'text-gray-900'],
                ['label' => 'Cicilan Berikutnya', 'value' => 'Rp ' . number_format($summary['next'], 0, ',', '.'), 'color' => 'text-yellow-600'],
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
            <p class="mt-1">Data pinjaman akan tampil setelah HR/admin menghubungkan akun ini ke Employee Profile.</p>
        </div>
    @endif

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Pinjaman</h2>
            <p class="text-sm text-gray-500">Informasi ini hanya untuk monitoring karyawan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Pinjaman</th>
                        <th class="px-5 py-3">Cicilan</th>
                        <th class="px-5 py-3">Saldo</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($loans as $loan)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $loan->loan_number }}</div>
                                <div class="text-xs text-gray-500">{{ str_replace('_', ' ', $loan->loan_type) }} · mulai {{ $loan->start_period?->format('M Y') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">Rp {{ number_format((float) $loan->monthly_installment, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">{{ $loan->installments->where('status', 'posted')->count() }}/{{ $loan->tenor_months }} terbayar</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-900">Rp {{ number_format((float) $loan->outstanding_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ str_replace('_', ' ', $loan->payroll_posting_status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada data pinjaman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
