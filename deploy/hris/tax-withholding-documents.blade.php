@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Bukti Potong</h1>
            <p class="mt-1 text-sm text-gray-500">Generate e-Bupot / bukti potong PPh 21 dari paket laporan pajak bulanan.</p>
        </div>
        <a href="{{ route('payroll.tax-report-packages') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-file-export"></i>
            Laporan Pajak
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
                ['label' => 'Dokumen', 'value' => $summary['documents'], 'color' => 'text-blue-600'],
                ['label' => 'Published', 'value' => $summary['published'], 'color' => 'text-green-600'],
                ['label' => 'Draft', 'value' => $summary['draft'], 'color' => 'text-yellow-600'],
                ['label' => 'Total PPh 21', 'value' => 'IDR ' . number_format($summary['total_pph21'], 0, ',', '.'), 'color' => 'text-red-600'],
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
            <h2 class="text-lg font-semibold text-gray-900">Generate Bukti Potong</h2>
            <p class="text-sm text-gray-500">Pilih paket laporan yang sudah berisi detail karyawan dan PPh 21 final.</p>
        </div>
        <form action="{{ route('payroll.tax-withholding-documents.generate') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-3">
            @csrf
            <select name="tax_monthly_report_package_id" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Pilih paket laporan</option>
                @foreach($packages as $package)
                    <option value="{{ $package->id }}">{{ $package->package_number }} - {{ $package->period }} - {{ $package->company?->code ?? 'All' }}</option>
                @endforeach
            </select>
            <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="draft">Draft</option>
                <option value="published">Published ke ESS</option>
            </select>
            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-file-signature"></i>
                Generate
            </button>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Dokumen Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Nomor</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Bruto</th>
                        <th class="px-5 py-3">Dasar Pajak</th>
                        <th class="px-5 py-3">PPh 21</th>
                        <th class="px-5 py-3">Checksum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($documents as $document)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $document->document_number }}</td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $document->taxProfile?->employee_name }}</div>
                                <div class="text-xs text-gray-500">{{ $document->taxProfile?->employee_number }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $document->period }}</td>
                            <td class="px-5 py-4">{{ ucfirst($document->status) }}</td>
                            <td class="px-5 py-4">IDR {{ number_format($document->gross_income, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">IDR {{ number_format($document->tax_base, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-semibold text-red-600">IDR {{ number_format($document->pph21_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ substr($document->checksum, 0, 16) }}...</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada bukti potong.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
