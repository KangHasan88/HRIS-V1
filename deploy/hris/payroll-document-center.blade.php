@extends('layouts.sidebar')

@section('title', 'Pusat Dokumen Payroll')

@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Pusat Dokumen Payroll</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau publish slip gaji, bukti potong, acknowledgement ESS, resend, checksum, dan kesiapan dokumen payroll.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('payroll.payslips') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fas fa-file-invoice mr-2"></i> Slip Gaji
            </a>
            <a href="{{ route('payroll.tax-withholding-documents') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-file-signature mr-2"></i> Bukti Potong
            </a>
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Readiness', 'value' => $summary['readiness'].'%', 'color' => $summary['readiness'] >= 80 ? 'text-green-600' : 'text-amber-600', 'sub' => 'Siap ESS'],
                ['label' => 'Slip Published', 'value' => $summary['published_payslips'].'/'.$summary['payslips'], 'color' => 'text-blue-600', 'sub' => 'Total slip'],
                ['label' => 'Ack Karyawan', 'value' => $summary['acknowledged_payslips'], 'color' => 'text-green-600', 'sub' => $summary['resend_count'].' resend'],
                ['label' => 'Bukti Potong', 'value' => $summary['published_tax_documents'].'/'.$summary['tax_documents'], 'color' => 'text-blue-600', 'sub' => $summary['draft_tax_documents'].' draft'],
                ['label' => 'Net Pay', 'value' => $money($summary['net_pay']), 'color' => 'text-gray-900', 'sub' => 'Total slip'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,0.9fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Checklist Publish</h2>
                    <p class="text-sm text-gray-500">Item bermasalah sebaiknya dibereskan sebelum closing payroll period.</p>
                </div>
                <div class="group relative">
                    <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                    <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                        Halaman ini tidak mengganti form slip atau bukti potong. Fungsinya sebagai pusat kontrol untuk memastikan dokumen payroll sudah dipublish ke ESS, karyawan sudah acknowledge, dan dokumen pajak tidak tertinggal dalam status draft.
                    </div>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($checks as $check)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $check['label'] }}</div>
                            <div class="mt-1 text-sm text-gray-500">{{ $check['note'] }}</div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">{{ $check['value'] }} / {{ $check['target'] }}</div>
                        <div class="flex items-center gap-2 md:justify-end">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ in_array($check['status'], ['Siap ESS', 'Lengkap', 'Terkontrol'], true) ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ $check['status'] }}</span>
                            <a href="{{ route($check['route']) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Detail</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Workflow Dokumen</h2>
                <p class="text-sm text-gray-500">Alur publish yang disarankan untuk payroll dan pajak.</p>
            </div>
            <div class="mt-4 space-y-3">
                @foreach([
                    ['step' => '1', 'title' => 'Review payroll run', 'route' => 'payroll.calculation', 'desc' => 'Pastikan gross, potongan, PPh 21, dan net pay sudah benar.'],
                    ['step' => '2', 'title' => 'Publish slip gaji', 'route' => 'payroll.payslips', 'desc' => 'Slip published otomatis muncul di ESS karyawan.'],
                    ['step' => '3', 'title' => 'Generate bukti potong', 'route' => 'payroll.tax-withholding-documents', 'desc' => 'Dokumen pajak dibuat dari paket laporan final.'],
                    ['step' => '4', 'title' => 'Pantau ESS acknowledgement', 'route' => 'payroll.payslips', 'desc' => 'Follow-up slip yang belum dikonfirmasi karyawan.'],
                    ['step' => '5', 'title' => 'Siapkan Coretax', 'route' => 'payroll.coretax-integration', 'desc' => 'Pastikan template, queue, dan rekonsiliasi siap.'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="block rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                        <div class="flex gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">{{ $item['step'] }}</span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ $item['title'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-gray-500">{{ $item['desc'] }}</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Slip Gaji Terbaru</h2>
                <p class="text-sm text-gray-500">Status publish, acknowledgment, dan resend notifikasi.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($payslips as $payslip)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $payslip->payslip_number }}</div>
                            <div class="text-sm text-gray-500">{{ $payslip->employee?->full_name ?? '-' }} · {{ $payslip->period }} · {{ $payslip->employee?->company?->code ?? '-' }}</div>
                            <div class="mt-1 font-mono text-xs text-gray-400">{{ $payslip->checksum ? substr($payslip->checksum, 0, 18).'...' : '-' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">{{ $money($payslip->net_pay) }}</div>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $payslip->status === 'published' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($payslip->status) }}</span>
                            <div class="mt-1 text-xs text-gray-500">{{ $payslip->acknowledged_at ? 'Ack '.$payslip->acknowledged_at->format('d M H:i') : 'Belum ack' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada slip gaji.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Bukti Potong Terbaru</h2>
                <p class="text-sm text-gray-500">Dokumen pajak yang siap ditampilkan di ESS.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($taxDocuments as $document)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $document->document_number }}</div>
                            <div class="text-sm text-gray-500">{{ $document->taxProfile?->employee_name ?? '-' }} · {{ $document->period }} · {{ $document->document_type }}</div>
                            <div class="mt-1 font-mono text-xs text-gray-400">{{ $document->checksum ? substr($document->checksum, 0, 18).'...' : '-' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-red-600">{{ $money($document->pph21_amount) }}</div>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $document->status === 'published' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($document->status) }}</span>
                            <div class="mt-1 text-xs text-gray-500">{{ $document->published_at ? 'Published '.$document->published_at->format('d M H:i') : 'Belum publish' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada bukti potong.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
