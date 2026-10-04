@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Master PPh 21</h1>
            <p class="mt-1 text-sm text-gray-500">Profil pajak karyawan: NIK, NPWP, PTKP, metode pajak, KLU, dan KPP cabang.</p>
        </div>
        <a href="{{ route('hris.payroll') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-wallet"></i>
            Payroll
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-3">
            @foreach([
                ['label' => 'Profil Pajak', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'NPWP Verified', 'value' => $summary['verified_npwp'], 'color' => 'text-green-600'],
                ['label' => 'Tanpa NPWP', 'value' => $summary['without_npwp'], 'color' => 'text-yellow-600'],
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
            <h2 class="text-lg font-semibold text-gray-900">Tambah Profil PPh 21</h2>
            <p class="text-sm text-gray-500">Data ini menjadi sumber payroll tax dan integrasi Coretax berikutnya.</p>
        </div>
        <form action="{{ route('payroll.tax-profiles.store') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-4">
            @csrf
            <select name="company_id" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Pilih company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                @endforeach
            </select>
            <select name="branch_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Cabang opsional</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                @endforeach
            </select>
            <input name="employee_number" placeholder="NIK karyawan internal" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="employee_name" required placeholder="Nama karyawan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="nik" required placeholder="NIK KTP" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="npwp" placeholder="NPWP" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="ptkp_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">PTKP</option>
                @foreach($ptkpOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
            <select name="tax_method" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                @foreach($taxMethods as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <input name="klu" placeholder="KLU" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="kpp_code" placeholder="Kode KPP" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="kpp_name" placeholder="Nama KPP cabang" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                <input type="checkbox" name="is_npwp_verified" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                NPWP verified
            </label>
            <div class="xl:col-span-4 flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Profil Pajak
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Profil Terbaru</h2>
            <p class="text-sm text-gray-500">Maksimal 100 profil pajak terakhir.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Company</th>
                        <th class="px-5 py-3">NIK / NPWP</th>
                        <th class="px-5 py-3">PTKP</th>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3">KPP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($profiles as $profile)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $profile->employee_name }}</div>
                                <div class="text-xs text-gray-500">{{ $profile->employee_number ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $profile->company?->code }}</div>
                                <div class="text-xs text-gray-500">{{ $profile->branch?->code ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs text-gray-700">{{ $profile->nik }}</div>
                                <div class="font-mono text-xs {{ $profile->is_npwp_verified ? 'text-green-600' : 'text-gray-500' }}">{{ $profile->npwp ?: 'Tanpa NPWP' }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $profile->ptkp_status }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $taxMethods[$profile->tax_method] ?? $profile->tax_method }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $profile->kpp_code ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $profile->kpp_name ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada profil PPh 21.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
