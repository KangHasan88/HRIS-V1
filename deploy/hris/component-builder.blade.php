@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Builder Komponen Payroll</h1>
            <p class="mt-1 text-sm text-gray-500">Master pendapatan, potongan, benefit, formula, prorata, dan rule eligibility payroll.</p>
        </div>
        <a href="{{ route('payroll.tax-monthly') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-percent"></i> PPh 21 Bulanan
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Total Komponen', 'value' => $summary['components'], 'color' => 'text-blue-600'],
                ['label' => 'Pendapatan', 'value' => $summary['earnings'], 'color' => 'text-green-600'],
                ['label' => 'Potongan', 'value' => $summary['deductions'], 'color' => 'text-red-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-indigo-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(360px,0.8fr)]">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Komponen Payroll</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai builder komponen payroll',
                        'description' => 'Form ini dipakai payroll admin untuk membuat master komponen gaji sebelum dipakai dalam payroll run, payslip, pajak, BPJS, dan laporan.',
                        'steps' => [
                            'Tentukan kategori komponen: pendapatan, potongan, benefit, kontribusi perusahaan, atau informasi.',
                            'Pilih metode hitung: fixed, formula, rate, attendance based, atau manual.',
                            'Isi formula/dependency jika komponen bergantung ke attendance, base salary, atau komponen lain.',
                            'Aktifkan taxable, BPJS basis, dan tampil di slip sesuai kebutuhan.',
                        ],
                        'notes' => [
                            'Formula disimpan sebagai expression untuk dipakai payroll engine berikutnya.',
                            'Prorata menentukan perlakuan join/resign atau absensi pada periode payroll.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Komponen ini adalah fondasi payroll engine dan integrasi pajak/BPJS.</p>
            </div>

            <form action="{{ route('payroll.components.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Komponen</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Komponen</label>
                            <input name="component_code" required placeholder="Contoh: BASIC, MEAL, BPJS-TK" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Komponen</label>
                            <input name="component_name" required placeholder="Contoh: Gaji Pokok" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Semua perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</label>
                            <select name="component_category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="earning">Pendapatan</option>
                                <option value="deduction">Potongan</option>
                                <option value="benefit">Benefit</option>
                                <option value="employer_contribution">Kontribusi perusahaan</option>
                                <option value="information">Informasi slip</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Formula & Prorata</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Metode Hitung</label>
                            <select name="calculation_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="fixed">Fixed amount</option>
                                <option value="formula">Formula</option>
                                <option value="rate">Rate / persentase</option>
                                <option value="attendance_based">Berbasis absensi</option>
                                <option value="manual">Manual input</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Basis Prorata</label>
                            <select name="prorate_basis" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="none">Tidak prorata</option>
                                <option value="calendar_days">Hari kalender</option>
                                <option value="working_days">Hari kerja</option>
                                <option value="attendance_days">Hari hadir</option>
                                <option value="join_resign_days">Join/resign days</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Default Amount</label>
                            <input type="number" name="default_amount" min="0" step="1" placeholder="Contoh: 5000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Rate</label>
                            <input type="number" name="rate_value" min="0" step="0.000001" placeholder="Contoh: 0.05" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Formula Expression</label>
                            <input name="formula_expression" placeholder="Contoh: base_salary / working_days * attendance_days" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dependencies</label>
                            <input name="dependencies" placeholder="Pisahkan koma, contoh: base_salary,working_days,attendance_days" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Perlakuan Payroll</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_taxable" value="1"> Kena pajak
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_bpjs_basis" value="1"> Basis BPJS
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_payslip_visible" value="1" checked> Tampil di slip
                        </label>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">GL Account</label>
                            <input name="gl_account" placeholder="Contoh: 5110-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Berlaku</label>
                            <input type="date" name="effective_start" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Berlaku</label>
                            <input type="date" name="effective_end" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Urutan</label>
                            <input type="number" name="sort_order" min="0" value="10" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                            <textarea name="notes" rows="2" placeholder="Catatan payroll, pajak, atau pengecualian." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Komponen
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Rule Eligibility</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara pakai rule eligibility',
                        'description' => 'Rule menentukan kapan komponen payroll ikut dihitung, dikecualikan, atau perlu review manual.',
                        'steps' => [
                            'Pilih komponen payroll.',
                            'Tentukan field kondisi seperti employment status, grade, department, branch, atau attendance days.',
                            'Pilih operator dan action yang harus dilakukan payroll engine.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Contoh: tunjangan site hanya untuk cabang tertentu atau potongan hanya untuk status tertentu.</p>
            </div>

            <form action="{{ route('payroll.components.rules') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Komponen</label>
                    <select name="payroll_component_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih komponen</option>
                        @foreach($components as $component)
                            <option value="{{ $component->id }}">{{ $component->component_code }} - {{ $component->component_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Rule</label>
                    <input name="rule_name" required placeholder="Contoh: Hanya karyawan permanen" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Field Kondisi</label>
                        <select name="condition_field" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="employment_status">Employment status</option>
                            <option value="grade">Grade</option>
                            <option value="department">Department</option>
                            <option value="branch">Branch</option>
                            <option value="attendance_days">Attendance days</option>
                            <option value="join_date">Join date</option>
                            <option value="resign_date">Resign date</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Operator</label>
                        <select name="operator" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="equals">Equals</option>
                            <option value="not_equals">Not equals</option>
                            <option value="contains">Contains</option>
                            <option value="gte">Greater/equal</option>
                            <option value="lte">Lower/equal</option>
                            <option value="between">Between</option>
                        </select>
                    </div>
                </div>
                <input name="condition_value" placeholder="Contoh: permanent / BMP-SITE-01 / 20" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <div class="grid gap-3 sm:grid-cols-2">
                    <select name="action" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="include">Include</option>
                        <option value="exclude">Exclude</option>
                        <option value="override_amount">Override amount</option>
                        <option value="require_review">Require review</option>
                    </select>
                    <input type="number" name="priority" value="10" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" checked> Rule aktif
                </label>
                <textarea name="notes" rows="3" placeholder="Catatan rule." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-code-branch"></i> Simpan Rule
                </button>
            </form>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Komponen Payroll</h2>
            <p class="text-sm text-gray-500">Komponen aktif akan menjadi sumber konfigurasi payroll run dan payslip.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Komponen</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3">Formula</th>
                        <th class="px-5 py-3">Payroll</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($components as $component)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $component->component_code }}</div>
                                <div class="text-xs text-gray-500">{{ $component->component_name }}</div>
                            </td>
                            <td class="px-5 py-4">{{ ucfirst(str_replace('_', ' ', $component->component_category)) }}</td>
                            <td class="px-5 py-4">{{ ucfirst(str_replace('_', ' ', $component->calculation_method)) }}</td>
                            <td class="px-5 py-4">
                                <div class="max-w-md text-xs text-gray-600">{{ $component->formula_expression ?: '-' }}</div>
                                <div class="mt-1 text-xs text-gray-400">{{ implode(', ', $component->dependencies ?? []) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($component->is_taxable)<span class="rounded-full bg-red-50 px-2 py-1 text-xs font-semibold text-red-700">Taxable</span>@endif
                                    @if($component->is_bpjs_basis)<span class="rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-green-700">BPJS</span>@endif
                                    @if($component->is_payslip_visible)<span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">Slip</span>@endif
                                </div>
                            </td>
                            <td class="px-5 py-4"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($component->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Belum ada komponen payroll.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
