@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Slip Gaji</h1>
        <p class="mt-1 text-sm text-gray-500">Unduh slip gaji yang sudah dipublish dan konfirmasi penerimaan dokumen.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if(! $employee)
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
            Profil karyawan belum terhubung ke akun ini.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($payslips as $payslip)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $payslip->period }}</div>
                            <h2 class="mt-1 text-base font-semibold text-gray-900">{{ $payslip->payslip_number }}</h2>
                            <p class="mt-1 text-xs text-gray-500">{{ $payslip->employee_snapshot['position'] ?? 'Employee' }}</p>
                        </div>
                        <span class="rounded-full {{ $payslip->acknowledged_at ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }} px-3 py-1 text-xs font-semibold">
                            {{ $payslip->acknowledged_at ? 'Sudah konfirmasi' : 'Belum konfirmasi' }}
                        </span>
                    </div>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Bruto</span>
                            <span class="font-semibold text-gray-900">Rp {{ number_format((float) $payslip->gross_pay, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Potongan</span>
                            <span class="font-semibold text-red-600">Rp {{ number_format((float) $payslip->total_deduction, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Take Home Pay</span>
                            <span class="font-semibold text-green-600">Rp {{ number_format((float) $payslip->net_pay, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg bg-gray-50 p-3 font-mono text-xs text-gray-500">
                        Checksum: {{ substr($payslip->checksum, 0, 20) }}...
                    </div>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <a href="{{ route('ess.payslips.download', $payslip) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            <i class="fas fa-download"></i>
                            Download
                        </a>
                        <form action="{{ route('ess.payslips.acknowledge', $payslip) }}" method="POST">
                            @csrf
                            <button @disabled($payslip->acknowledged_at) class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400">
                                <i class="fas fa-check"></i>
                                Konfirmasi
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                    Belum ada slip gaji yang dipublish untuk akun ini.
                </div>
            @endforelse
        </div>
    @endif
</div>
@endsection
