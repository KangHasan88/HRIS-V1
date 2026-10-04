@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Laporan Pajak Bulanan</h1>
            <p class="mt-1 text-sm text-gray-500">Paket rekap PPh 21 bulanan yang siap divalidasi sebelum ekspor Coretax.</p>
        </div>
        <a href="{{ route('payroll.tax-monthly') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-calculator"></i>
            Hitung PPh 21
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Paket', 'value' => $summary['packages'], 'color' => 'text-blue-600'],
                ['label' => 'Final', 'value' => $summary['final'], 'color' => 'text-green-600'],
                ['label' => 'Karyawan', 'value' => $summary['employees'], 'color' => 'text-yellow-600'],
                ['label' => 'Template', 'value' => $summary['templates'], 'color' => 'text-blue-600'],
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
            <h2 class="text-lg font-semibold text-gray-900">Buat Paket Laporan</h2>
            <p class="text-sm text-gray-500">Ambil semua kalkulasi final di periode yang dipilih dan simpan snapshot untuk audit/export.</p>
        </div>
        <form action="{{ route('payroll.tax-report-packages.store') }}" method="POST" class="mt-5 grid gap-4 xl:grid-cols-5">
            @csrf
            <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="">Semua company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                @endforeach
            </select>
            <input type="month" name="period" required value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <select name="status" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <option value="draft">Draft</option>
                <option value="final">Final</option>
                <option value="ready_for_coretax">Ready for Coretax</option>
            </select>
            <input name="notes" placeholder="Catatan validasi" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-box-archive"></i>
                Buat Paket
            </button>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Paket Terbaru</h2>
            <p class="text-sm text-gray-500">Snapshot laporan PPh 21 bulanan yang siap dikunci atau dikirim ke proses Coretax.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Paket</th>
                        <th class="px-5 py-3">Company</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Bruto</th>
                        <th class="px-5 py-3">Dasar Pajak</th>
                        <th class="px-5 py-3">PPh 21</th>
                        <th class="px-5 py-3">Validasi</th>
                        <th class="px-5 py-3">Template</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($packages as $package)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $package->package_number }}</div>
                                <div class="text-xs text-gray-500">{{ $package->created_at?->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $package->company?->code ?? 'All' }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $package->period }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ str_replace('_', ' ', ucfirst($package->status)) }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $package->employee_count }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($package->total_gross_income, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($package->total_tax_base, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-semibold text-red-600">IDR {{ number_format($package->total_pph21, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-xs text-gray-500">
                                {{ empty($package->validation_notes) ? 'OK' : implode(' | ', $package->validation_notes) }}
                            </td>
                            <td class="px-5 py-4">
                                <form action="{{ route('payroll.tax-report-packages.templates', $package) }}" method="POST" class="flex min-w-[360px] items-center gap-2">
                                    @csrf
                                    <select name="schema_version" class="rounded-lg border border-gray-300 px-3 py-2 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <option value="coretax-v1.0">coretax-v1.0</option>
                                        <option value="coretax-v1.1">coretax-v1.1</option>
                                    </select>
                                    <select name="format" class="rounded-lg border border-gray-300 px-3 py-2 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <option value="csv">CSV</option>
                                        <option value="xlsx">XLSX</option>
                                        <option value="json">JSON</option>
                                    </select>
                                    <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-xs outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <option value="generated">Generated</option>
                                        <option value="validated">Validated</option>
                                        <option value="ready_to_export">Ready</option>
                                    </select>
                                    <button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Generate</button>
                                </form>
                                <div class="mt-2 text-xs text-gray-500">{{ $package->exportTemplates->count() }} template</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada paket laporan pajak bulanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Template Ekspor Coretax</h2>
            <p class="text-sm text-gray-500">Versioning schema, payload snapshot, dan checksum untuk kontrol perubahan template.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Template</th>
                        <th class="px-5 py-3">Paket</th>
                        <th class="px-5 py-3">Schema</th>
                        <th class="px-5 py-3">Format</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Rows</th>
                        <th class="px-5 py-3">Checksum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $template->template_number }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $template->package?->package_number }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $template->schema_version }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ strtoupper($template->format) }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ str_replace('_', ' ', ucfirst($template->status)) }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ count($template->payload_snapshot ?? []) }}</td>
                            <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ substr($template->checksum, 0, 16) }}...</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada template ekspor Coretax.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
