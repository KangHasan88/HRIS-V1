@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Simulasi PPh 21</h1>
            <p class="mt-1 text-sm text-gray-500">Uji skenario bulanan, THR, bonus, pajak final, dan gross-up sebelum payroll final.</p>
        </div>
        <a href="{{ route('payroll.tax-monthly') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-percent"></i> Hitung PPh 21
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Scenario', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Review', 'value' => $summary['review'], 'color' => 'text-yellow-600'],
                ['label' => 'THR/Bonus', 'value' => $summary['thr_bonus'], 'color' => 'text-blue-600'],
                ['label' => 'Rata-rata PPh 21', 'value' => 'Rp ' . number_format($summary['avg_pph21'], 0, ',', '.'), 'color' => 'text-red-600'],
                ['label' => 'Tampil ESS', 'value' => $summary['visible'], 'color' => 'text-green-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[.75fr_1.25fr]">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Scenario</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara kerja Simulasi PPh 21',
                        'description' => 'Simulasi ini tidak mengubah payroll final. Hasilnya dipakai payroll untuk membandingkan metode pajak dan dampak THP sebelum diposting.',
                        'steps' => [
                            'Pilih profil pajak karyawan dan jenis scenario.',
                            'Isi komponen reguler, penghasilan tidak teratur seperti THR/bonus, potongan, dan pajak final jika ada.',
                            'Pilih metode gross, gross-up, net, company borne, atau tax allowance.',
                            'Set visible to ESS hanya jika hasil sudah approved dan boleh dilihat karyawan.',
                        ],
                        'notes' => [
                            'Gross-up dihitung iteratif agar tunjangan pajak mendekati nilai PPh 21 final.',
                            'Penghasilan tidak teratur dipisah agar THR/bonus tidak bercampur dengan gaji reguler.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Simpan sebagai draft atau ready review sebelum dipakai payroll.</p>
            </div>

            <form action="{{ route('payroll.tax-simulations.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Scenario</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Profil Pajak</label>
                            <select name="tax_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih karyawan pajak</option>
                                @foreach($profiles as $profile)
                                    <option value="{{ $profile->id }}">{{ $profile->employee_number }} - {{ $profile->employee_name }} ({{ $profile->ptkp_status }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Scenario</label>
                            <input name="scenario_name" required placeholder="Contoh: THR Juli gross-up" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Scenario</label>
                            <select name="scenario_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="monthly">Bulanan</option>
                                <option value="thr">THR</option>
                                <option value="bonus">Bonus</option>
                                <option value="final_tax">Pajak final</option>
                                <option value="gross_up">Gross-up khusus</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode</label>
                            <input type="month" name="period" required value="{{ now()->format('Y-m') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Metode Pajak</label>
                            <select name="tax_method" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="gross">Gross</option>
                                <option value="gross_up">Gross-up</option>
                                <option value="net">Net</option>
                                <option value="company_borne">Pajak ditanggung perusahaan</option>
                                <option value="tax_allowance">Tunjangan pajak</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Komponen Penghasilan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Gaji Bruto Reguler</label>
                            <input type="number" step="0.01" name="gross_income" required placeholder="Contoh: 12000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tunjangan Kena Pajak</label>
                            <input type="number" step="0.01" name="taxable_allowance" required value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">THR / Bonus / Tidak Teratur</label>
                            <input type="number" step="0.01" name="irregular_income" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Potongan Pajak</label>
                            <input type="number" step="0.01" name="deduction" required value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Pajak Final</label>
                            <input type="number" step="0.01" name="final_tax_amount" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Scenario</label>
                            <select name="scenario_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="ready_for_review">Ready review</option>
                                <option value="approved_for_payroll">Approved payroll</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm md:col-span-2">
                            <input type="checkbox" name="visible_to_employee" value="1" class="rounded border-gray-300 text-blue-600">
                            Tampilkan hasil approved ke ESS karyawan
                        </label>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Catatan</h3>
                    <textarea name="notes" rows="3" placeholder="Catatan scenario, dasar asumsi THR/bonus, atau keputusan payroll." class="mt-3 w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-flask"></i> Simpan Simulasi
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Scenario</h2>
                <p class="text-sm text-gray-500">Hasil simulasi, THP, dan selisih terhadap payroll final jika tersedia.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Simulasi</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Jenis</th>
                            <th class="px-5 py-3">Dasar Pajak</th>
                            <th class="px-5 py-3">PPh 21</th>
                            <th class="px-5 py-3">THP</th>
                            <th class="px-5 py-3">Gross-up</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($simulations as $simulation)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $simulation->simulation_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $simulation->scenario_name }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $simulation->taxProfile?->employee_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $simulation->taxProfile?->employee_number }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">{{ str_replace('_', ' ', ucfirst($simulation->scenario_type ?? 'monthly')) }}</div>
                                    <div class="text-xs text-gray-500">{{ str_replace('_', ' ', ucfirst($simulation->tax_method)) }} · {{ $simulation->period }}</div>
                                </td>
                                <td class="px-5 py-4 text-gray-600">Rp {{ number_format($simulation->tax_base, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 font-semibold text-red-600">Rp {{ number_format($simulation->pph21_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 font-semibold text-green-600">Rp {{ number_format($simulation->take_home_pay, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <div class="text-gray-600">Rp {{ number_format($simulation->tax_allowance_amount, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">{{ (int) ($simulation->gross_up_iterations ?? 0) }} iterasi</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ str_replace('_', ' ', ucfirst($simulation->scenario_status)) }}</span>
                                    <div class="mt-2 text-xs text-gray-500">{{ $simulation->visible_to_employee ? 'Tampil ESS' : 'Internal' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada simulasi pajak.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
