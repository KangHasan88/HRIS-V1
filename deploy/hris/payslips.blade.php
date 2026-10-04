@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Slip Gaji</h1>
            <p class="mt-1 text-sm text-gray-500">Publish slip gaji ke ESS, pantau download, acknowledgement, dan resend notifikasi.</p>
        </div>
        <a href="{{ route('ess.payslips') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-file-download"></i>
            Preview ESS
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
                ['label' => 'Total Slip', 'value' => $summary['total'], 'color' => 'text-blue-600'],
                ['label' => 'Published', 'value' => $summary['published'], 'color' => 'text-green-600'],
                ['label' => 'Diakui Karyawan', 'value' => $summary['acknowledged'], 'color' => 'text-indigo-600'],
                ['label' => 'Resend', 'value' => $summary['resend'], 'color' => 'text-yellow-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Buat / Publish Slip</h2>
                <p class="text-sm text-gray-500">Satu slip mewakili satu karyawan dan satu periode payroll.</p>
            </div>
            <div class="group relative">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-80 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    Isi komponen pendapatan dan potongan, pilih Draft untuk review internal, atau Published agar slip muncul di ESS karyawan. Tombol resend hanya aktif untuk slip published.
                </div>
            </div>
        </div>

        <form action="{{ route('payroll.payslips.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="rounded-xl border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900">Data Slip</h3>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</span>
                        <select name="employee_profile_id" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Periode Payroll</span>
                        <input name="period" type="month" required value="{{ now()->format('Y-m') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status Publish</span>
                        <select name="status" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="draft">Draft internal payroll</option>
                            <option value="published">Published ke ESS</option>
                        </select>
                    </label>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Pendapatan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <input name="basic_salary" type="number" min="0" step="1000" required placeholder="Gaji pokok" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="allowance" type="number" min="0" step="1000" placeholder="Tunjangan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="overtime" type="number" min="0" step="1000" placeholder="Lembur" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Potongan</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <input name="bpjs_deduction" type="number" min="0" step="1000" placeholder="BPJS" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="pph21_deduction" type="number" min="0" step="1000" placeholder="PPh 21" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <input name="other_deduction" type="number" min="0" step="1000" placeholder="Potongan lain" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                <textarea name="notes" rows="2" placeholder="Catatan payroll, misalnya rapel, koreksi, atau instruksi khusus." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Simpan Slip
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Slip Gaji</h2>
            <p class="text-sm text-gray-500">Status publish dan acknowledgement dari karyawan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Nomor</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Net Pay</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Ack</th>
                        <th class="px-5 py-3">Resend</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payslips as $payslip)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $payslip->payslip_number }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $payslip->employee?->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $payslip->employee?->employee_number }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $payslip->period }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-900">Rp {{ number_format((float) $payslip->net_pay, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full {{ $payslip->status === 'published' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }} px-3 py-1 text-xs font-semibold">{{ ucfirst($payslip->status) }}</span>
                            </td>
                            <td class="px-5 py-4">{{ $payslip->acknowledged_at ? $payslip->acknowledged_at->format('d M Y H:i') : '-' }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $payslip->resend_count }}x</div>
                                <div class="text-xs text-gray-500">{{ $payslip->last_sent_at?->format('d M Y H:i') ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('payroll.payslips.resend', $payslip) }}" method="POST">
                                    @csrf
                                    <button @disabled($payslip->status !== 'published') class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400">
                                        <i class="fas fa-paper-plane"></i>
                                        Resend
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada slip gaji.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
