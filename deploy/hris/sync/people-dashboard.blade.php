@extends('layouts.sidebar')

@section('title', 'People Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">People Analytics</h1>
            <p class="mt-1 text-sm text-gray-500">Dashboard headcount, turnover, absensi, cuti/lembur, workforce eksternal, dan biaya payroll.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('analytics.workforce-planning') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                <i class="fas fa-people-arrows"></i> Workforce Planning
            </a>
            <a href="{{ route('payroll.document-center') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-folder-open"></i> Dokumen Payroll
            </a>
        </div>
    </div>

    <section class="panel rounded-2xl p-5">
        <form method="GET" action="{{ route('analytics.people-dashboard') }}" class="grid gap-3 lg:grid-cols-[minmax(220px,1fr)_minmax(220px,1fr)_160px_160px_auto] lg:items-end">
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
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua site</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) $filters['branch_id'] === $branch->id)>{{ $branch->code }} - {{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dari</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sampai</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-filter"></i> Terapkan
            </button>
        </form>
    </section>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4 xl:grid-cols-6">
            @foreach([
                ['label' => 'Headcount Aktif', 'value' => $summary['active_employee'], 'hint' => 'Employee aktif', 'color' => 'text-gray-900'],
                ['label' => 'Join Baru', 'value' => $summary['joined'], 'hint' => 'Periode filter', 'color' => 'text-green-600'],
                ['label' => 'Turnover', 'value' => $summary['turnover_rate'].'%', 'hint' => $summary['ended'].' exit/nonaktif', 'color' => $summary['turnover_rate'] > 5 ? 'text-red-600' : 'text-green-600'],
                ['label' => 'Late Rate', 'value' => $summary['late_rate'].'%', 'hint' => $summary['clock_in'].' clock-in', 'color' => $summary['late_rate'] > 10 ? 'text-red-600' : 'text-blue-600'],
                ['label' => 'Cuti', 'value' => $summary['leave_days'], 'hint' => 'Hari diminta', 'color' => 'text-amber-600'],
                ['label' => 'Lembur', 'value' => $summary['overtime_hours'], 'hint' => 'Jam diminta', 'color' => 'text-blue-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(360px,.8fr)]">
        <section class="panel rounded-2xl p-5">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Ringkasan Biaya & Kapasitas</h2>
                    <p class="text-sm text-gray-500">Biaya payroll, reimbursement, business trip, dan workforce eksternal pada scope terpilih.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">People Cost</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['label' => 'Net Payroll', 'value' => 'Rp '.number_format($summary['payroll_cost'], 0, ',', '.'), 'icon' => 'fas fa-wallet', 'color' => 'text-blue-600'],
                    ['label' => 'Gross Payslip', 'value' => 'Rp '.number_format($summary['gross_pay'], 0, ',', '.'), 'icon' => 'fas fa-file-invoice-dollar', 'color' => 'text-green-600'],
                    ['label' => 'Reimbursement', 'value' => 'Rp '.number_format($summary['reimbursement'], 0, ',', '.'), 'icon' => 'fas fa-receipt', 'color' => 'text-amber-600'],
                    ['label' => 'Worker Eksternal', 'value' => $summary['external_workers'], 'icon' => 'fas fa-user-gear', 'color' => 'text-gray-900'],
                ] as $cost)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $cost['label'] }}</div>
                            <i class="{{ $cost['icon'] }} text-gray-400"></i>
                        </div>
                        <div class="mt-3 text-xl font-semibold {{ $cost['color'] }}">{{ $cost['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Breakdown Departemen</h3>
                    <div class="mt-3 space-y-3">
                        @forelse($departmentBreakdown as $row)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $row['label'] }}</span>
                                    <span class="text-gray-500">{{ $row['value'] }} orang</span>
                                </div>
                                <div class="mt-1 h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-blue-600" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-500">Belum ada data departemen.</div>
                        @endforelse
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Breakdown Site</h3>
                    <div class="mt-3 space-y-3">
                        @forelse($branchBreakdown as $row)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $row['label'] }}</span>
                                    <span class="text-gray-500">{{ $row['value'] }} orang</span>
                                </div>
                                <div class="mt-1 h-2 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-green-600" style="width: {{ $row['percent'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-500">Belum ada data site.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Insight HR</h2>
                <p class="text-sm text-gray-500">Sinyal ringkas untuk prioritas follow-up.</p>
            </div>
            <div class="mt-4 space-y-3">
                @foreach($insights as $insight)
                    @php
                        $tone = [
                            'good' => 'border-green-100 bg-green-50 text-green-700',
                            'warning' => 'border-amber-100 bg-amber-50 text-amber-700',
                            'risk' => 'border-red-100 bg-red-50 text-red-700',
                            'neutral' => 'border-blue-100 bg-blue-50 text-blue-700',
                        ][$insight['tone']] ?? 'border-gray-100 bg-gray-50 text-gray-700';
                    @endphp
                    <div class="rounded-xl border px-4 py-3 {{ $tone }}">
                        <div class="text-sm font-semibold">{{ $insight['title'] }}</div>
                        <p class="mt-1 text-sm leading-5">{{ $insight['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Trend Clock-in</h2>
                <p class="text-sm text-gray-500">Jumlah clock-in dan indikasi terlambat per tanggal.</p>
            </div>
            <div class="p-5">
                <div class="space-y-3">
                    @forelse($attendanceTrend as $row)
                        @php $width = $summary['clock_in'] > 0 ? max(5, (int) round(($row['clock_in'] / max(1, $summary['clock_in'])) * 100)) : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700">{{ $row['label'] }}</span>
                                <span class="text-gray-500">{{ $row['clock_in'] }} clock-in, {{ $row['late'] }} telat</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-blue-600" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-8 text-center text-sm text-gray-500">Belum ada data clock-in pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Payroll Run Terbaru</h2>
                <p class="text-sm text-gray-500">Run payroll yang menjadi dasar people cost dashboard.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Run</th>
                            <th class="px-5 py-3">Employee</th>
                            <th class="px-5 py-3">Net</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payrollRuns as $run)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $run->run_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $run->period }}</div>
                                </td>
                                <td class="px-5 py-4">{{ $run->employee_count }}</td>
                                <td class="px-5 py-4">Rp {{ number_format((float) $run->net_estimate, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-600">{{ ucfirst($run->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Belum ada payroll run pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
