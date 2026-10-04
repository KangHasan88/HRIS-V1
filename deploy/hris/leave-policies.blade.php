@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Kebijakan Cuti</h1>
            <p class="mt-1 text-sm text-gray-500">Atur jenis cuti, kuota, carry forward, unpaid leave, dan aturan scope perusahaan/site.</p>
        </div>
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
                ['label' => 'Jenis Cuti', 'value' => $summary['types'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Carry Forward', 'value' => $summary['carry_forward'], 'color' => 'text-yellow-600'],
                ['label' => 'Aturan Scope', 'value' => $summary['rules'], 'color' => 'text-gray-900'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.05fr_.95fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Jenis Cuti</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Jenis Cuti',
                        'description' => 'Form ini dipakai untuk mendefinisikan master jenis cuti dan perilakunya saat dihitung saldo atau payroll.',
                        'steps' => [
                            'Isi kode, nama, kategori, satuan, kuota tahunan, dan batas hari per pengajuan.',
                            'Aktifkan carry forward jika sisa cuti boleh dibawa ke periode berikutnya.',
                            'Atur paid/unpaid, lampiran wajib, approval, prorata join date, dan masa kerja minimum.',
                        ],
                        'notes' => [
                            'Jenis cuti adalah master. Aturan scope dibuat di form Aturan Berlaku.',
                            'Unpaid leave sebaiknya tidak paid dan tidak membutuhkan saldo.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Master ini dipakai saat simulasi saldo dan pengajuan cuti.</p>
            </div>

            <form action="{{ route('leave.policies.types') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Cuti</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Cuti</label>
                            <input name="code" required placeholder="Contoh: ANNUAL" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Cuti</label>
                            <input name="name" required placeholder="Contoh: Cuti Tahunan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</label>
                            <select name="category" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @foreach($categoryOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Satuan</label>
                            <select name="unit" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="day">Hari</option>
                                <option value="half_day">Setengah hari</option>
                                <option value="hour">Jam</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="active">Aktif</option>
                                <option value="draft">Draft</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Kuota & Carry Forward</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kuota Tahunan</label>
                            <input type="number" step="0.5" min="0" name="annual_quota_days" required value="12" placeholder="Hari" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Max per Pengajuan</label>
                            <input type="number" min="1" name="max_days_per_request" placeholder="Contoh: 5" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Max Carry Forward</label>
                            <input type="number" step="0.5" min="0" name="carry_forward_max_days" required value="0" placeholder="Hari" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Expired Carry</label>
                            <input type="number" min="0" max="24" name="carry_forward_expiry_months" required value="0" placeholder="Bulan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <label class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="requires_balance" value="1" checked class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span><span class="block font-medium text-gray-800">Butuh saldo</span><span class="text-xs text-gray-500">Pengajuan memotong saldo cuti.</span></span>
                        </label>
                        <label class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="carry_forward_allowed" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span><span class="block font-medium text-gray-800">Carry forward</span><span class="text-xs text-gray-500">Sisa saldo bisa dibawa ke tahun berikutnya.</span></span>
                        </label>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Syarat & Dampak</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Gender Berlaku</label>
                            <select name="gender_rule" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="all">Semua</option>
                                <option value="female">Perempuan</option>
                                <option value="male">Laki-laki</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Minimum Masa Kerja</label>
                            <input type="number" min="0" max="240" name="min_service_months" required value="0" placeholder="Bulan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Payroll</label>
                            <select name="is_paid" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="1">Paid</option>
                                <option value="0">Unpaid</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-3">
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="approval_required" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            Wajib approval
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="requires_attachment" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            Wajib lampiran
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="prorate_on_join" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            Prorata join date
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 sm:col-span-3">
                            <input type="checkbox" name="negative_balance_allowed" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            Izinkan saldo minus untuk kasus khusus yang disetujui HR.
                        </label>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Deskripsi</label>
                        <textarea name="description" rows="3" placeholder="Jelaskan aturan khusus, dokumen pendukung, atau dampak payroll." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Jenis Cuti
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Aturan Berlaku</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan Aturan Berlaku',
                        'description' => 'Form ini menentukan jenis cuti berlaku untuk perusahaan, site, dan status karyawan tertentu.',
                        'steps' => [
                            'Pilih jenis cuti yang sudah dibuat.',
                            'Tentukan scope perusahaan, cabang/site, status karyawan, dan periode efektif.',
                            'Isi frekuensi accrual, jumlah saldo yang bertambah, dan bulan reset saldo.',
                        ],
                        'notes' => [
                            'Kosongkan perusahaan/site jika aturan berlaku global.',
                            'Centang hari libur/weekend jika tanggal tersebut tetap dihitung sebagai cuti.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Scope ini dipakai saat sistem memilih policy untuk karyawan.</p>
            </div>
            <form action="{{ route('leave.policies.rules') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Scope Policy</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Cuti</label>
                            <select name="leave_policy_type_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih jenis cuti</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->code }} - {{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Global semua perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                            <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua cabang/site</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Karyawan</label>
                            <select name="employment_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @foreach($employmentStatusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Aturan</label>
                            <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="active">Aktif</option>
                                <option value="draft">Draft</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Accrual & Periode</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Efektif</label>
                            <input type="date" name="effective_start" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Akhir Efektif</label>
                            <input type="date" name="effective_end" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Frekuensi Accrual</label>
                            <select name="accrual_frequency" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="yearly">Tahunan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="anniversary">Anniversary join date</option>
                                <option value="none">Tidak ada accrual</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Jumlah Accrual</label>
                            <input type="number" step="0.5" min="0" name="accrual_days" required value="12" placeholder="Hari" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bulan Reset</label>
                            <input type="number" min="1" max="12" name="reset_month" required value="1" placeholder="1-12" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <label class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="include_holidays" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span><span class="block font-medium text-gray-800">Hari libur ikut dihitung</span><span class="text-xs text-gray-500">Libur nasional/site tetap memotong durasi cuti.</span></span>
                        </label>
                        <label class="flex items-start gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="include_weekends" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span><span class="block font-medium text-gray-800">Weekend ikut dihitung</span><span class="text-xs text-gray-500">Sabtu/Minggu tetap masuk durasi cuti.</span></span>
                        </label>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Aturan</label>
                        <textarea name="notes" rows="3" placeholder="Catatan scope, pengecualian site, atau aturan approval tambahan." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-save"></i>
                    Simpan Aturan
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Kebijakan Cuti</h2>
            <p class="text-sm text-gray-500">Policy ini menjadi dasar saldo cuti, validasi pengajuan, dan dampak payroll.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Jenis</th>
                        <th class="px-5 py-3">Kuota</th>
                        <th class="px-5 py-3">Rule</th>
                        <th class="px-5 py-3">Dampak</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($types as $type)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $type->code }} - {{ $type->name }}</div>
                                <div class="text-xs text-gray-500">{{ $categoryOptions[$type->category] ?? $type->category }} · {{ $type->unit }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ number_format((float) $type->annual_quota_days, 1) }} hari</div>
                                <div class="text-xs text-gray-500">Max request: {{ $type->max_days_per_request ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $type->rules_count }} aturan</div>
                                <div class="text-xs text-gray-500">Min kerja: {{ $type->min_service_months }} bulan</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-500">
                                {{ $type->is_paid ? 'Paid' : 'Unpaid' }} · {{ $type->requires_balance ? 'Potong saldo' : 'Tanpa saldo' }} · {{ $type->carry_forward_allowed ? 'Carry' : 'No carry' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $type->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($type->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada kebijakan cuti.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
