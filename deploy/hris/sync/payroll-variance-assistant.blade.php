@extends('layouts.sidebar')

@section('title', 'Asisten Varians Payroll')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Asisten Varians Payroll</h1>
            <p class="mt-1 text-sm text-gray-500">Bantu Payroll menjelaskan perubahan gross, deduction, net pay, dan anomali sebelum slip dipublish.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('payroll.run-wizard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <i class="fas fa-list-check"></i> Wizard Payroll
            </a>
            <a href="{{ route('analytics.people-dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-chart-pie"></i> People Analytics
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <form method="GET" action="{{ route('analytics.payroll-variance-assistant') }}" class="grid gap-3 lg:grid-cols-[minmax(220px,1fr)_180px_180px_auto] lg:items-end">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Company</label>
                <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected((int) $filters['company_id'] === $company->id)>{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Periode</label>
                <input type="month" name="period" value="{{ $filters['period'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Severity</label>
                <select name="severity" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach(['critical', 'high', 'medium', 'low'] as $severity)
                        <option value="{{ $severity }}" @selected($filters['severity'] === $severity)>{{ ucfirst($severity) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-filter"></i> Terapkan
            </button>
        </form>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-3 xl:grid-cols-6">
            @foreach([
                ['label' => 'Payroll Run', 'value' => $summary['runs'], 'hint' => 'Scope aktif', 'color' => 'text-blue-600'],
                ['label' => 'Anomali', 'value' => $summary['anomalies'], 'hint' => 'Terbaca', 'color' => 'text-amber-600'],
                ['label' => 'Critical/High', 'value' => $summary['critical_high'], 'hint' => 'Prioritas review', 'color' => $summary['critical_high'] > 0 ? 'text-red-600' : 'text-green-600'],
                ['label' => 'Open', 'value' => $summary['open'], 'hint' => 'Belum selesai', 'color' => $summary['open'] > 0 ? 'text-red-600' : 'text-green-600'],
                ['label' => 'Validasi Failed', 'value' => $summary['failed_validation'], 'hint' => 'Blocking rule', 'color' => $summary['failed_validation'] > 0 ? 'text-red-600' : 'text-green-600'],
                ['label' => 'Net Variance', 'value' => 'Rp '.number_format($summary['net_variance'], 0, ',', '.'), 'hint' => 'Delta total', 'color' => $summary['net_variance'] >= 0 ? 'text-blue-600' : 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(360px,.9fr)]">
        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Penjelasan Varians</h2>
                    @include('partials.form-help', [
                        'title' => 'Cara membaca asisten varians payroll',
                        'description' => 'Halaman ini merangkum anomali dari payroll run dan memberi penjelasan operasional agar payroll tahu data apa yang perlu dicek.',
                        'steps' => [
                            'Filter company, periode, atau severity untuk fokus review.',
                            'Baca penjelasan varians untuk melihat arah perubahan dan kemungkinan penyebab.',
                            'Gunakan rekomendasi sebagai checklist sebelum slip atau transfer dipublish.',
                            'Update status anomali setelah diverifikasi payroll/HR.',
                        ],
                        'notes' => [
                            'Ini asisten review berbasis rule dan evidence, bukan keputusan final otomatis.',
                            'Keputusan final tetap di payroll lead atau approver sesuai workflow.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Narasi otomatis berbasis anomaly code, severity, evidence, dan perubahan nominal.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($explanations as $explanation)
                    @php
                        $severityClass = [
                            'critical' => 'bg-red-50 text-red-700',
                            'high' => 'bg-orange-50 text-orange-700',
                            'medium' => 'bg-amber-50 text-amber-700',
                            'low' => 'bg-blue-50 text-blue-700',
                        ][$explanation['severity']] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <article class="p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $explanation['title'] }}</h3>
                                <p class="mt-1 text-xs font-medium text-gray-500">{{ $explanation['subtitle'] }}</p>
                            </div>
                            <div class="flex gap-2">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $severityClass }}">{{ ucfirst($explanation['severity']) }}</span>
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">{{ ucfirst($explanation['status']) }}</span>
                            </div>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-gray-600">{{ $explanation['body'] }}</p>
                        <div class="mt-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                            <span class="font-semibold">Rekomendasi:</span> {{ $explanation['recommendation'] }}
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada anomali payroll pada filter ini.</div>
                @endforelse
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Validasi Bermasalah</h2>
                <p class="text-sm text-gray-500">Rule failed/warning yang perlu dicek sebelum run lanjut.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($validations as $validation)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $validation->check_name }}</div>
                                <div class="mt-1 font-mono text-xs text-gray-500">{{ $validation->payrollRun?->run_number }} - {{ $validation->check_code }}</div>
                            </div>
                            <span class="rounded-full {{ $validation->status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700' }} px-2 py-1 text-xs font-semibold">{{ ucfirst($validation->status) }}</span>
                        </div>
                        <p class="mt-3 text-sm leading-5 text-gray-600">{{ $validation->message }}</p>
                        <div class="mt-2 text-xs text-gray-500">{{ $validation->affected_count }} data terdampak</div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Tidak ada validasi failed/warning pada filter ini.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Anomali Payroll</h2>
            <p class="text-sm text-gray-500">Update status setelah dicek ke komponen payroll, absensi, pajak, BPJS, atau adjustment.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Anomali</th>
                        <th class="px-5 py-3">Nilai</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Review</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($anomalies as $anomaly)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $anomaly->employee?->full_name ?: '-' }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $anomaly->employee?->employee_number ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $anomaly->employee?->branch?->code ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $anomaly->anomaly_name }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $anomaly->payrollRun?->run_number }} - {{ $anomaly->anomaly_code }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $anomaly->message }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-500">Sebelum: Rp {{ number_format((float) $anomaly->previous_amount, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">Saat ini: Rp {{ number_format((float) $anomaly->current_amount, 0, ',', '.') }}</div>
                                <div class="mt-1 font-semibold {{ (float) $anomaly->variance_percent >= 20 ? 'text-red-600' : 'text-amber-600' }}">{{ number_format((float) $anomaly->variance_percent, 1) }}%</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($anomaly->status) }}</span>
                                <div class="mt-2 text-xs text-gray-500">{{ ucfirst($anomaly->severity) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route('analytics.payroll-variance-assistant.review', $anomaly) }}" class="space-y-2">
                                    @csrf
                                    <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                        @foreach(['open', 'reviewed', 'resolved', 'ignored'] as $status)
                                            <option value="{{ $status }}" @selected($anomaly->status === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                    <input name="review_note" placeholder="Catatan review singkat" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs">
                                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                        <i class="fas fa-check"></i> Simpan Review
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada anomali payroll pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
