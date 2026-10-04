@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">PPh 21 Non Permanen</h1>
        <p class="mt-1 text-sm text-gray-500">Aturan pajak pekerja harian, freelancer, konsultan, dan bukan pegawai.</p>
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
                ['label' => 'Worker', 'value' => $summary['workers'], 'color' => 'text-blue-600'],
                ['label' => 'Kalkulasi', 'value' => $summary['calculations'], 'color' => 'text-yellow-600'],
                ['label' => 'Final', 'value' => $summary['final'], 'color' => 'text-green-600'],
                ['label' => 'Total Pajak', 'value' => 'IDR ' . number_format($summary['total_tax'], 0, ',', '.'), 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Tambah Worker</h2>
            <form action="{{ route('payroll.tax-non-permanent.workers') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <input name="worker_name" required placeholder="Nama worker" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input name="worker_number" placeholder="Nomor worker" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="worker_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach($workerTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select name="ptkp_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach(['TK/0','TK/1','TK/2','TK/3','K/0','K/1','K/2','K/3'] as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
                <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <select name="branch_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                    @endforeach
                </select>
                <input name="nik" placeholder="NIK" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input name="npwp" placeholder="NPWP" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_npwp_verified" value="1">
                    NPWP verified
                </label>
                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Simpan Worker</button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Hitung PPh 21</h2>
            <form action="{{ route('payroll.tax-non-permanent.calculate') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <select name="tax_non_permanent_worker_id" required class="md:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Pilih worker</option>
                    @foreach($workers as $worker)
                        <option value="{{ $worker->id }}">{{ $worker->worker_name }} - {{ $workerTypes[$worker->worker_type] ?? $worker->worker_type }}</option>
                    @endforeach
                </select>
                <input type="month" name="period" required value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="number" name="work_days" required min="1" max="31" value="1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="number" name="daily_rate" required min="0" step="1000" placeholder="Rate harian / fee" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="number" name="tax_rate" required min="0" max="1" step="0.000001" placeholder="Tarif, contoh 0.05" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="calculation_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="draft">Draft</option>
                    <option value="final">Final</option>
                </select>
                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Hitung</button>
                </div>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Kalkulasi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Worker</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Bruto</th>
                        <th class="px-5 py-3">Dasar Pajak</th>
                        <th class="px-5 py-3">PPh 21</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($calculations as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $item->worker?->worker_name }}</div>
                                <div class="text-xs text-gray-500">{{ $workerTypes[$item->worker?->worker_type] ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $item->period }}</td>
                            <td class="px-5 py-4">IDR {{ number_format($item->gross_income, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">IDR {{ number_format($item->taxable_base, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-semibold text-red-600">IDR {{ number_format($item->pph21_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada kalkulasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
