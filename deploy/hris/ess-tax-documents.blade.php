@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Bukti Potong PPh 21</h1>
        <p class="mt-1 text-sm text-gray-500">Dokumen PPh 21 yang sudah dipublish oleh payroll.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($documents as $document)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $document->period }}</div>
                        <h2 class="mt-1 text-base font-semibold text-gray-900">{{ $document->document_number }}</h2>
                    </div>
                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-600">{{ ucfirst($document->status) }}</span>
                </div>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Bruto</span>
                        <span class="font-semibold text-gray-900">IDR {{ number_format($document->gross_income, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Dasar Pajak</span>
                        <span class="font-semibold text-gray-900">IDR {{ number_format($document->tax_base, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">PPh 21</span>
                        <span class="font-semibold text-red-600">IDR {{ number_format($document->pph21_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="mt-5 rounded-lg bg-gray-50 p-3 font-mono text-xs text-gray-500">
                    Checksum: {{ substr($document->checksum, 0, 20) }}...
                </div>
                <button class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                    <i class="fas fa-download"></i>
                    Download Metadata
                </button>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                Belum ada bukti potong yang dipublish untuk akun ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
