@extends('layouts.sidebar')

@section('title', 'Builder Laporan HR')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Builder Laporan HR</h1>
            <p class="mt-1 text-sm text-gray-500">Buat narasi laporan dari data HR aktual untuk leader, HRBP, payroll, atau direksi.</p>
        </div>
        <a href="{{ route('analytics.people-dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-chart-pie mr-2"></i> People Analytics
        </a>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Fokus Terdeteksi', 'value' => $intent['label'], 'color' => 'text-blue-600', 'sub' => $intent['confidence'].'% confidence'],
                ['label' => 'Headcount', 'value' => $metrics['headcount'], 'color' => 'text-gray-900', 'sub' => $metrics['new_joiners'].' join baru'],
                ['label' => 'Late Rate', 'value' => $metrics['late_rate'].'%', 'color' => $metrics['late_rate'] >= 15 ? 'text-red-600' : 'text-green-600', 'sub' => $metrics['late_clock_ins'].' late clock-in'],
                ['label' => 'Lembur', 'value' => $metrics['overtime_hours'], 'color' => 'text-amber-600', 'sub' => 'Jam diminta'],
                ['label' => 'Engagement', 'value' => $metrics['avg_engagement'], 'color' => $metrics['avg_engagement'] > 0 && $metrics['avg_engagement'] < 70 ? 'text-red-600' : 'text-green-600', 'sub' => $metrics['engagement_responses'].' response'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Prompt Laporan</h2>
                <p class="text-sm text-gray-500">Tulis kebutuhan laporan dengan bahasa natural, lalu sistem susun narasi dari metrik HR.</p>
            </div>
            <div class="group relative">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    Builder ini membaca prompt untuk menentukan fokus laporan, lalu mengambil angka dari data HRIS. Narasi dibuat dari metrik yang terlihat di tabel, sehingga leader bisa menelusuri sumber angka sebelum mengambil keputusan.
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('analytics.hr-report-builder') }}" class="mt-5 space-y-4">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Instruksi Laporan</label>
                <textarea name="prompt" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm leading-6 focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: Buat ringkasan absensi dan payroll untuk weekly meeting leader.">{{ $filters['prompt'] }}</textarea>
            </div>
            <div class="grid gap-4 lg:grid-cols-6">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Dari</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sampai</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Company</label>
                    <select name="company_id" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" @selected((int) $filters['company_id'] === $company->id)>{{ $company->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                    <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua cabang/site</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((int) $filters['branch_id'] === $branch->id)>{{ $branch->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Audience</label>
                    <select name="audience" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="leader" @selected($filters['audience'] === 'leader')>Leader</option>
                        <option value="hrbp" @selected($filters['audience'] === 'hrbp')>HRBP</option>
                        <option value="payroll" @selected($filters['audience'] === 'payroll')>Payroll</option>
                        <option value="direksi" @selected($filters['audience'] === 'direksi')>Direksi</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-file-lines mr-2"></i> Buat Laporan
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($promptExamples as $example)
                <a href="{{ route('analytics.hr-report-builder', array_merge($filters, ['prompt' => $example])) }}" class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-white">
                    {{ $example }}
                </a>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(400px,0.9fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ $report['title'] }}</h2>
                <p class="text-sm text-gray-500">Periode {{ $report['period'] }}. Narasi ini bisa dipakai sebagai bahan review, bukan keputusan final otomatis.</p>
            </div>
            <div class="space-y-4 p-5">
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-blue-600">Prompt</div>
                    <div class="mt-1 text-sm leading-6 text-gray-700">{{ $report['prompt'] }}</div>
                </div>
                @foreach($report['paragraphs'] as $paragraph)
                    <p class="text-sm leading-7 text-gray-700">{{ $paragraph }}</p>
                @endforeach
                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                    <h3 class="font-semibold text-gray-900">Sinyal Risiko</h3>
                    <div class="mt-3 space-y-2">
                        @foreach($report['risks'] as $risk)
                            <div class="flex gap-2 text-sm text-gray-700">
                                <i class="fas fa-triangle-exclamation mt-0.5 text-amber-600"></i>
                                <span>{{ $risk }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Action Item</h2>
                <p class="text-sm text-gray-500">Rekomendasi follow-up berdasarkan metrik yang terbaca.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($report['actions'] as $index => $action)
                    <div class="flex gap-3 px-5 py-4">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">{{ $index + 1 }}</span>
                        <div class="text-sm leading-6 text-gray-700">{{ $action }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Metrik Pendukung</h2>
            <p class="text-sm text-gray-500">Angka di bawah ini menjadi dasar narasi laporan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Group</th>
                        <th class="px-5 py-3 text-left">Metrik</th>
                        <th class="px-5 py-3 text-left">Nilai</th>
                        <th class="px-5 py-3 text-left">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($report['table'] as $row)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $row['group'] }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $row['metric'] }}</td>
                            <td class="px-5 py-4 font-semibold text-blue-600">{{ $row['value'] }}</td>
                            <td class="px-5 py-4 text-gray-500">{{ $row['note'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
