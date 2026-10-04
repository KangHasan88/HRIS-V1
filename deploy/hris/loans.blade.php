@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Pinjaman & Potongan</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola pinjaman karyawan, potongan koperasi, cicilan, dan saldo payroll.</p>
        </div>
        <a href="{{ route('ess.loans') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-mobile-screen-button"></i> Preview ESS
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
                ['label' => 'Total Pinjaman', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Disetujui', 'value' => $summary['approved'], 'color' => 'text-green-600'],
                ['label' => 'Aktif Payroll', 'value' => $summary['posting'], 'color' => 'text-blue-600'],
                ['label' => 'Saldo Belum Lunas', 'value' => 'Rp ' . number_format($summary['outstanding'], 0, ',', '.'), 'color' => 'text-gray-900'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Pinjaman</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara kerja Pinjaman & Potongan',
                        'description' => 'Form ini membuat pinjaman atau potongan berkala yang nanti dipakai payroll sebagai jadwal cicilan.',
                        'steps' => [
                            'Pilih karyawan, tipe pinjaman, nominal pokok, tenor, dan periode mulai potong.',
                            'Sistem otomatis membuat jadwal cicilan bulanan sesuai tenor.',
                            'Set status aktif jika cicilan sudah boleh diproses di payroll.',
                        ],
                        'notes' => [
                            'Potongan koperasi memakai tipe Cooperative Loan agar mudah dipisah di report.',
                            'Cicilan yang sudah diposting akan mengurangi saldo belum lunas.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Dipakai untuk loan, koperasi, kasbon, dan potongan berkala lain.</p>
            </div>

            <form action="{{ route('payroll.loans.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Data Pinjaman</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Pinjaman</label>
                            <input name="loan_number" required placeholder="Contoh: LOAN-202607-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tipe</label>
                            <select name="loan_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="employee_loan">Pinjaman karyawan</option>
                                <option value="cooperative_loan">Potongan koperasi</option>
                                <option value="cash_advance">Kasbon</option>
                                <option value="other_deduction">Potongan lain</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Pinjaman</label>
                            <input type="date" name="loan_date" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Penyedia</label>
                            <input name="provider" placeholder="Contoh: Koperasi Karyawan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Nominal & Cicilan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Pokok</label>
                            <input type="number" name="principal_amount" min="1" step="1" required placeholder="Contoh: 5000000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tenor Bulan</label>
                            <input type="number" name="tenor_months" value="10" min="1" max="120" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Admin Fee</label>
                            <input type="number" name="admin_fee" value="0" min="0" step="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Bunga / Margin</label>
                            <input type="number" name="interest_amount" value="0" min="0" step="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai Potong</label>
                            <input type="date" name="start_period" value="{{ now()->startOfMonth()->toDateString() }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Komponen Payroll</label>
                            <input name="deduction_component_code" placeholder="Contoh: DED-LOAN" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Status</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Approval</label>
                            <select name="approval_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="submitted">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="draft">Draft</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Posting Payroll</label>
                            <select name="payroll_posting_status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="not_posted">Belum aktif</option>
                                <option value="active">Aktif dipotong</option>
                                <option value="paused">Pause</option>
                                <option value="completed">Selesai</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</label>
                            <textarea name="notes" rows="3" placeholder="Catatan approval, dasar potongan, atau nomor kontrak koperasi." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-save"></i> Simpan Pinjaman
                    </button>
                </div>
            </form>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Pinjaman</h2>
                <p class="text-sm text-gray-500">Monitoring saldo dan jadwal cicilan payroll.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Pinjaman</th>
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Saldo</th>
                            <th class="px-5 py-3">Cicilan</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($loans as $loan)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $loan->loan_number }}</div>
                                    <div class="text-xs text-gray-500">{{ str_replace('_', ' ', $loan->loan_type) }} · {{ $loan->loan_date?->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $loan->provider ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $loan->employeeProfile?->full_name ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $loan->employeeProfile?->employee_number ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">Rp {{ number_format((float) $loan->outstanding_amount, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">Dibayar Rp {{ number_format((float) $loan->paid_amount, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">Rp {{ number_format((float) $loan->monthly_installment, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">{{ $loan->installments->where('status', 'posted')->count() }}/{{ $loan->tenor_months }} terbayar</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ ucfirst($loan->approval_status) }}</span>
                                    <div class="mt-2 text-xs text-gray-500">{{ str_replace('_', ' ', $loan->payroll_posting_status) }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada pinjaman karyawan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
