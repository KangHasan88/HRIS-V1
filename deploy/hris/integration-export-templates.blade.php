@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Export Pajak & BPJS</h1>
            <p class="mt-1 text-sm text-gray-500">Template ekspor CSV dengan validasi data sebelum dikirim ke Coretax, BPJS, atau payroll partner.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('integrations.export-templates.download', 'tax') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                <i class="fas fa-file-csv"></i>
                Download Pajak
            </a>
            <a href="{{ route('integrations.export-templates.download', 'bpjs') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                <i class="fas fa-file-csv"></i>
                Download BPJS
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Row Pajak', 'value' => $summary['tax_rows'], 'color' => 'text-blue-600'],
                ['label' => 'Pajak Valid', 'value' => $summary['tax_valid'], 'color' => 'text-green-600'],
                ['label' => 'Row BPJS', 'value' => $summary['bpjs_rows'], 'color' => 'text-indigo-600'],
                ['label' => 'BPJS Valid', 'value' => $summary['bpjs_valid'], 'color' => 'text-green-600'],
                ['label' => 'Perlu Review', 'value' => $summary['issues'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Preview Template Pajak</h2>
                <p class="text-sm text-gray-500">Validasi NIK, NPWP, PTKP, metode pajak, dan KPP.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">NPWP/PTKP</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($taxRows as $row)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $row['employee_name'] }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $row['employee_number'] ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-mono text-xs text-gray-700">{{ $row['npwp'] ?: 'Tanpa NPWP' }}</div>
                                    <div class="text-xs text-gray-500">{{ $row['ptkp_status'] ?: '-' }} / {{ $row['tax_method'] ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $row['valid'] ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} px-3 py-1 text-xs font-semibold">{{ $row['validation_status'] }}</span>
                                    <div class="mt-1 text-xs text-gray-500">{{ $row['validation_notes'] ?: 'OK' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada profil pajak.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Preview Template BPJS</h2>
                <p class="text-sm text-gray-500">Validasi nomor BPJS, status verified, active flag, dan rate iuran.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Karyawan</th>
                            <th class="px-5 py-3">Nomor BPJS</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bpjsRows as $row)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $row['employee_name'] }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $row['employee_number'] ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-mono text-xs text-gray-700">Kes: {{ $row['bpjs_kesehatan'] ?: '-' }}</div>
                                    <div class="font-mono text-xs text-gray-700">TK: {{ $row['bpjs_ketenagakerjaan'] ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $row['valid'] ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} px-3 py-1 text-xs font-semibold">{{ $row['validation_status'] }}</span>
                                    <div class="mt-1 text-xs text-gray-500">{{ $row['validation_notes'] ?: 'OK' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada profil BPJS payroll.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
