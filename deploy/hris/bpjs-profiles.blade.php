@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">BPJS Payroll</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola nomor BPJS, kelas, dasar iuran, rate karyawan/perusahaan, dan link profil pajak.</p>
        </div>
        <a href="{{ route('payroll.tax-profiles') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-file-invoice-dollar"></i>
            Master PPh 21
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
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-blue-600'],
                ['label' => 'Verified', 'value' => $summary['verified'], 'color' => 'text-green-600'],
                ['label' => 'Draft', 'value' => $summary['draft'], 'color' => 'text-yellow-600'],
                ['label' => 'Nonaktif', 'value' => $summary['inactive'], 'color' => 'text-gray-600'],
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
            <h2 class="text-lg font-semibold text-gray-900">Tambah Profil BPJS</h2>
            <p class="text-sm text-gray-500">Data ini dipakai untuk komponen payroll BPJS dan rekonsiliasi bulanan.</p>
        </div>
        <form action="{{ route('payroll.bpjs-profiles.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="grid gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Data Peserta</h3>
                        <p class="text-xs text-gray-500">Identitas karyawan, nomor peserta, dan kelas BPJS.</p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <select name="employee_profile_id" required class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }} / {{ $employee->company?->code ?? '-' }}</option>
                            @endforeach
                        </select>
                        <select name="tax_profile_id" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Link profil pajak opsional</option>
                            @foreach($taxProfiles as $taxProfile)
                                <option value="{{ $taxProfile->id }}">{{ $taxProfile->employee_name }} / {{ $taxProfile->nik }}</option>
                            @endforeach
                        </select>
                        <input name="bpjs_kesehatan_number" placeholder="No. BPJS Kesehatan" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="bpjs_ketenagakerjaan_number" placeholder="No. BPJS Ketenagakerjaan" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <select name="bpjs_class" required class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="Kelas 1">Kelas 1</option>
                            <option value="Kelas 2">Kelas 2</option>
                            <option value="Kelas 3">Kelas 3</option>
                        </select>
                        <input type="date" name="effective_date" required title="Tanggal efektif" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Dasar Iuran</h3>
                        <p class="text-xs text-gray-500">Nominal upah yang menjadi dasar hitung BPJS.</p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">BPJS Kesehatan</span>
                            <input type="number" name="health_contribution_base" required min="0" placeholder="Contoh: 12000000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">BPJS Ketenagakerjaan</span>
                            <input type="number" name="employment_contribution_base" required min="0" placeholder="Contoh: 10000000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </label>
                    </div>
                    <div class="mt-3 rounded-lg border border-blue-100 bg-white px-3 py-2 text-xs text-gray-500">
                        Dasar iuran bisa berbeda dari gaji pokok jika ada batas maksimum atau kebijakan payroll tertentu.
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Rate Iuran BPJS (%)</h3>
                        <p class="text-xs text-gray-500">Pisahkan potongan karyawan dan beban perusahaan untuk payroll.</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">Persentase</span>
                </div>
                <div class="grid gap-3 xl:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2">
                            <h4 class="text-sm font-semibold text-gray-900">BPJS Kesehatan</h4>
                            <span class="text-xs font-medium text-gray-400">KES</span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</span>
                                <input type="number" step="0.0001" name="health_employee_rate" required value="1" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</span>
                                <input type="number" step="0.0001" name="health_employer_rate" required value="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </label>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2">
                            <h4 class="text-sm font-semibold text-gray-900">Jaminan Hari Tua</h4>
                            <span class="text-xs font-medium text-gray-400">JHT</span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</span>
                                <input type="number" step="0.0001" name="jht_employee_rate" required value="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</span>
                                <input type="number" step="0.0001" name="jht_employer_rate" required value="3.7" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </label>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2">
                            <h4 class="text-sm font-semibold text-gray-900">Jaminan Pensiun</h4>
                            <span class="text-xs font-medium text-gray-400">JP</span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</span>
                                <input type="number" step="0.0001" name="jp_employee_rate" required value="1" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</span>
                                <input type="number" step="0.0001" name="jp_employer_rate" required value="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-900">Status & Catatan</h3>
                    <p class="text-xs text-gray-500">Kontrol apakah profil sudah verified dan aktif dipakai payroll.</p>
                </div>
                <div class="grid gap-3 md:grid-cols-[1fr_1fr_2fr]">
                    <select name="verification_status" required class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="draft">Draft</option>
                        <option value="verified">Verified</option>
                        <option value="needs_review">Perlu review</option>
                    </select>
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        BPJS aktif
                    </label>
                    <textarea name="notes" rows="2" placeholder="Catatan BPJS" class="resize-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan BPJS
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Profil BPJS Terbaru</h2>
            <p class="text-sm text-gray-500">Maksimal 150 profil terakhir.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Nomor BPJS</th>
                        <th class="px-5 py-3">Dasar Iuran</th>
                        <th class="px-5 py-3">Estimasi Iuran</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($profiles as $profile)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $profile->employeeProfile?->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $profile->employeeProfile?->employee_number }} / {{ $profile->employeeProfile?->branch?->code ?? '-' }}</div>
                                <div class="text-xs text-gray-500">Pajak: {{ $profile->taxProfile?->ptkp_status ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs text-gray-700">Kes: {{ $profile->bpjs_kesehatan_number ?: '-' }}</div>
                                <div class="font-mono text-xs text-gray-700">TK: {{ $profile->bpjs_ketenagakerjaan_number ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $profile->bpjs_class }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                Kesehatan: Rp {{ number_format((float) $profile->health_contribution_base, 0, ',', '.') }}
                                <div class="text-xs text-gray-500">TK: Rp {{ number_format((float) $profile->employment_contribution_base, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                Karyawan: Rp {{ number_format(($profile->calculation_snapshot['health_employee_amount'] ?? 0) + ($profile->calculation_snapshot['jht_employee_amount'] ?? 0) + ($profile->calculation_snapshot['jp_employee_amount'] ?? 0), 0, ',', '.') }}
                                <div class="text-xs text-gray-500">Perusahaan: Rp {{ number_format(($profile->calculation_snapshot['health_employer_amount'] ?? 0) + ($profile->calculation_snapshot['jht_employer_amount'] ?? 0) + ($profile->calculation_snapshot['jp_employer_amount'] ?? 0), 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ ucfirst(str_replace('_', ' ', $profile->verification_status)) }}</span>
                                <div class="mt-1 text-xs text-gray-500">{{ $profile->is_active ? 'Aktif' : 'Nonaktif' }} sejak {{ $profile->effective_date?->format('d M Y') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada profil BPJS payroll.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
