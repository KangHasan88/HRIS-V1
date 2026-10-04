@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Koreksi Pajak</h1>
            <p class="mt-1 text-sm text-gray-500">Pembetulan pelaporan, koreksi payroll retroaktif, refund, dan kurang bayar PPh 21.</p>
        </div>
        <a href="{{ route('payroll.tax-report-packages') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-file-export"></i>
            Laporan Pajak
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
                ['label' => 'Total Koreksi', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Pending Review', 'value' => $summary['pending'], 'color' => 'text-yellow-600'],
                ['label' => 'Approved', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Net Delta PPh 21', 'value' => 'IDR ' . number_format($summary['delta'], 0, ',', '.'), 'color' => $summary['delta'] >= 0 ? 'text-red-600' : 'text-green-600'],
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
            <h2 class="text-lg font-semibold text-gray-900">Buat Koreksi</h2>
            <p class="text-sm text-gray-500">Simpan nilai sebelum dan sesudah supaya pembetulan bisa diaudit dari payroll sampai Coretax.</p>
        </div>
        <form action="{{ route('payroll.tax-corrections.store') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-4">
            @csrf
            <select name="tax_profile_id" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Pilih karyawan pajak</option>
                @foreach($profiles as $profile)
                    <option value="{{ $profile->id }}">{{ $profile->employee_number }} - {{ $profile->employee_name }}</option>
                @endforeach
            </select>
            <select name="tax_monthly_report_package_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Tanpa paket laporan</option>
                @foreach($packages as $package)
                    <option value="{{ $package->id }}">{{ $package->package_number }} - {{ $package->period }}</option>
                @endforeach
            </select>
            <select name="correction_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="pembetulan_pelaporan">Pembetulan pelaporan</option>
                <option value="koreksi_payroll">Koreksi payroll</option>
                <option value="retroaktif">Retroaktif</option>
                <option value="refund">Refund</option>
                <option value="kurang_bayar">Kurang bayar</option>
            </select>
            <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="draft">Draft</option>
                <option value="submitted">Submitted</option>
                <option value="review">Review</option>
                <option value="approved">Approved</option>
                <option value="applied">Applied</option>
            </select>
            <input type="month" name="period" required value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="month" name="payroll_period" value="{{ now()->addMonth()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="payroll_adjustment_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="none">Tanpa adjustment payroll</option>
                <option value="next_payroll_refund">Refund payroll berikutnya</option>
                <option value="next_payroll_deduction">Potong payroll berikutnya</option>
                <option value="manual_transfer">Transfer manual</option>
                <option value="manual_journal">Jurnal manual</option>
            </select>
            <input name="reason" required placeholder="Alasan koreksi" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

            <input type="number" step="0.01" name="previous_gross_income" required placeholder="Bruto sebelumnya" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.01" name="corrected_gross_income" required placeholder="Bruto koreksi" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.01" name="previous_tax_base" required placeholder="Dasar pajak sebelumnya" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.01" name="corrected_tax_base" required placeholder="Dasar pajak koreksi" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.01" name="previous_pph21_amount" required placeholder="PPh 21 sebelumnya" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input type="number" step="0.01" name="corrected_pph21_amount" required placeholder="PPh 21 koreksi" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 xl:col-span-2">
                <i class="fas fa-rotate"></i>
                Simpan Koreksi
            </button>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Koreksi</h2>
            <p class="text-sm text-gray-500">Tracking pembetulan, dampak payroll, dan selisih PPh 21 per karyawan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Nomor</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Jenis</th>
                        <th class="px-5 py-3">Paket</th>
                        <th class="px-5 py-3">Sebelum</th>
                        <th class="px-5 py-3">Sesudah</th>
                        <th class="px-5 py-3">Delta</th>
                        <th class="px-5 py-3">Adjustment</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($corrections as $correction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $correction->correction_number }}</div>
                                <div class="text-xs text-gray-500">{{ $correction->created_at?->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $correction->taxProfile?->employee_name }}</div>
                                <div class="text-xs text-gray-500">{{ $correction->taxProfile?->employee_number }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $correction->period }}<div class="text-xs text-gray-500">Payroll: {{ $correction->payroll_period ?? '-' }}</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ str_replace('_', ' ', ucfirst($correction->correction_type)) }}</td>
                            <td class="px-5 py-4 text-xs text-gray-500">{{ $correction->package?->package_number ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($correction->previous_pph21_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($correction->corrected_pph21_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-semibold {{ $correction->delta_pph21_amount >= 0 ? 'text-red-600' : 'text-green-600' }}">IDR {{ number_format($correction->delta_pph21_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ str_replace('_', ' ', $correction->payroll_adjustment_type) }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ str_replace('_', ' ', ucfirst($correction->status)) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada koreksi pajak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
