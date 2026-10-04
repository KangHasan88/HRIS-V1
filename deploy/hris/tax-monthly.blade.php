@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Hitung PPh 21 Bulanan</h1>
            <p class="mt-1 text-sm text-gray-500">Mesin TER bulanan berbasis master tarif, profil PPh 21, dan audit log.</p>
        </div>
        <a href="{{ route('payroll.tax-audits') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-calculator"></i>
            Riwayat PPh 21
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
                ['label' => 'Tarif TER Aktif', 'value' => $summary['rates'], 'color' => 'text-blue-600'],
                ['label' => 'Kalkulasi', 'value' => $summary['calculations'], 'color' => 'text-yellow-600'],
                ['label' => 'Final', 'value' => $summary['final'], 'color' => 'text-green-600'],
                ['label' => 'Total PPh 21', 'value' => 'IDR ' . number_format($summary['total_pph21'], 0, ',', '.'), 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Master Tarif TER</h2>
                <p class="text-sm text-gray-500">Isi bracket tarif sesuai referensi pajak yang berlaku.</p>
            </div>
            <form action="{{ route('payroll.tax-monthly.rates') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <select name="category" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="A">Kategori A</option>
                    <option value="B">Kategori B</option>
                    <option value="C">Kategori C</option>
                </select>
                <div class="grid grid-cols-2 gap-3">
                    <input name="income_from" required type="number" min="0" step="1000" placeholder="Income dari" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <input name="income_to" type="number" min="0" step="1000" placeholder="Income sampai" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <input name="rate" required type="number" min="0" max="1" step="0.000001" placeholder="Rate, contoh 0.005" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="source_reference" placeholder="Referensi aturan/source" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-plus"></i>
                    Tambah Tarif
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Mapping Komponen Pajak</h2>
                <p class="text-sm text-gray-500">Hubungkan komponen payroll ke perlakuan pajak dan dependency formula.</p>
            </div>
            <form action="{{ route('payroll.tax-monthly.components') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-4">
                @csrf
                <input name="component_code" required placeholder="Kode, contoh MEAL" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="component_name" required placeholder="Nama komponen" class="xl:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="default_amount" type="number" min="0" step="1000" placeholder="Default amount" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <select name="component_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="earning">Earning</option>
                    <option value="deduction">Deduction</option>
                    <option value="benefit">Benefit</option>
                    <option value="employer_contribution">Employer contribution</option>
                </select>
                <select name="tax_treatment" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="taxable_income">Taxable income</option>
                    <option value="non_taxable">Non taxable</option>
                    <option value="deduction">Deduction</option>
                    <option value="company_cost">Company cost</option>
                </select>
                <input name="payroll_formula_key" placeholder="Formula key" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="dependencies" placeholder="Dependency, pisah koma" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_taxable" value="1">
                    Kena pajak
                </label>
                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_recurring" value="1" checked>
                    Recurring
                </label>
                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-link"></i>
                    Simpan Mapping
                </button>
            </form>
            <div class="mt-5 grid gap-3 lg:grid-cols-2">
                @forelse($components as $component)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $component->component_code }} - {{ $component->component_name }}</div>
                                <div class="text-xs text-gray-500">{{ $component->component_type }} / {{ $component->tax_treatment }}</div>
                            </div>
                            <div class="text-right text-sm font-semibold text-blue-600">IDR {{ number_format($component->default_amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">Formula: {{ $component->payroll_formula_key ?: '-' }} | Dependency: {{ implode(', ', $component->dependencies ?? []) ?: '-' }}</div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500">Belum ada mapping komponen pajak.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="border-b border-gray-100 pb-4">
            <h2 class="text-lg font-semibold text-gray-900">Hitung Bulanan</h2>
            <p class="text-sm text-gray-500">Sistem mencari bracket TER aktif berdasarkan kategori PTKP, metode pajak, dan komponen payroll terpilih.</p>
        </div>
        <form action="{{ route('payroll.tax-monthly.calculate') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-4">
            @csrf
            <select name="tax_profile_id" required class="xl:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Pilih profil pajak</option>
                @foreach($profiles as $profile)
                    <option value="{{ $profile->id }}">{{ $profile->employee_name }} - {{ $profile->ptkp_status }}</option>
                @endforeach
            </select>
            <input type="month" name="period" required value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="calculation_status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="draft">Draft</option>
                <option value="final">Final</option>
            </select>
            <select name="tax_method" required class="xl:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="gross">Gross - pajak dipotong karyawan</option>
                <option value="gross_up">Gross-up - tunjangan pajak otomatis</option>
                <option value="net">Net - input sebagai target THP</option>
                <option value="company_borne">Pajak ditanggung perusahaan</option>
                <option value="tax_allowance">Tunjangan pajak</option>
            </select>
            <input name="gross_income" required type="number" min="0" step="1000" placeholder="Gaji bruto" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="taxable_allowance" type="number" min="0" step="1000" placeholder="Tunjangan kena pajak" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <input name="deduction" type="number" min="0" step="1000" placeholder="Pengurang" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <div class="xl:col-span-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Komponen payroll yang ikut formula</div>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($components as $component)
                        <label class="flex items-start gap-3 rounded-lg bg-white p-3 text-sm text-gray-700 shadow-sm">
                            <input type="checkbox" name="component_ids[]" value="{{ $component->id }}" class="mt-1">
                            <span>
                                <span class="block font-semibold text-gray-900">{{ $component->component_code }} - {{ $component->component_name }}</span>
                                <span class="block text-xs text-gray-500">{{ $component->tax_treatment }} | IDR {{ number_format($component->default_amount, 0, ',', '.') }}</span>
                            </span>
                        </label>
                    @empty
                        <div class="text-sm text-gray-500">Belum ada komponen payroll yang bisa dipilih.</div>
                    @endforelse
                </div>
            </div>
            <div class="flex justify-end xl:col-span-4">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-calculator"></i>
                    Hitung
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Hasil TER Terbaru</h2>
            <p class="text-sm text-gray-500">100 kalkulasi PPh 21 bulanan terakhir.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3">Dasar TER</th>
                        <th class="px-5 py-3">Tunjangan Pajak</th>
                        <th class="px-5 py-3">Ditanggung Perusahaan</th>
                        <th class="px-5 py-3">Rate</th>
                        <th class="px-5 py-3">PPh 21</th>
                        <th class="px-5 py-3">THP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($calculations as $calculation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $calculation->taxProfile?->employee_name }}</div>
                                <div class="text-xs text-gray-500">{{ $calculation->source_snapshot['ptkp_status'] ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $calculation->period }}</div>
                                <span class="text-xs text-gray-500">{{ ucfirst($calculation->calculation_status) }}</span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $calculation->ter_category }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ str_replace('_', ' ', ucfirst($calculation->tax_method ?? 'gross')) }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($calculation->ter_base_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($calculation->tax_allowance_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($calculation->company_tax_borne_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ number_format($calculation->ter_rate * 100, 3, ',', '.') }}%</td>
                            <td class="px-5 py-4 font-semibold text-red-600">IDR {{ number_format($calculation->pph21_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-semibold text-green-600">IDR {{ number_format($calculation->take_home_pay ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada kalkulasi PPh 21 bulanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
