@extends('layouts.sidebar')

@section('title', 'Data Warehouse Export')

@section('content')
@php
    $statusClass = fn ($status) => $status === 'ready' || $status === 'healthy'
        ? 'bg-green-50 text-green-700'
        : 'bg-amber-50 text-amber-700';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Data Warehouse Export</h1>
            <p class="mt-1 text-sm text-gray-500">Siapkan dataset HRIS untuk BI, snapshot terjadwal, manifest export, dan quality gate.</p>
        </div>
        <a href="{{ route('settings.integration-health') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-heart-pulse mr-2"></i> Health Integrasi
        </a>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Dataset', 'value' => $summary['datasets'], 'color' => 'text-gray-900', 'sub' => 'Catalog BI'],
                ['label' => 'Siap Export', 'value' => $summary['ready'], 'color' => 'text-green-600', 'sub' => 'Dataset berisi data'],
                ['label' => 'Warning', 'value' => $summary['warning'], 'color' => $summary['warning'] > 0 ? 'text-amber-600' : 'text-green-600', 'sub' => 'Dataset kosong'],
                ['label' => 'Estimasi Rows', 'value' => number_format($summary['rows'], 0, ',', '.'), 'color' => 'text-blue-600', 'sub' => 'Total row export'],
                ['label' => 'Quality Issue', 'value' => $summary['quality_issue'], 'color' => $summary['quality_issue'] > 0 ? 'text-red-600' : 'text-green-600', 'sub' => 'Gate sebelum schedule'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Parameter Snapshot</h2>
                <p class="text-sm text-gray-500">Filter ini menentukan scope dan format manifest export BI.</p>
            </div>
            <div class="group relative">
                <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                    Halaman ini menyiapkan manifest dan quality gate sebelum export fisik diaktifkan. Untuk production, scheduler/worker akan memakai daftar dataset, partition period, destination, format, checksum, dan audit log dari manifest ini.
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('integrations.data-warehouse-export') }}" class="mt-5 grid gap-4 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Period</label>
                <input name="period" value="{{ $filters['period'] }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="2026-07">
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
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Format</label>
                <select name="format" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="parquet" @selected($filters['format'] === 'parquet')>Parquet</option>
                    <option value="csv" @selected($filters['format'] === 'csv')>CSV</option>
                    <option value="jsonl" @selected($filters['format'] === 'jsonl')>JSONL</option>
                </select>
            </div>
            <div class="flex items-end">
                <button class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    <i class="fas fa-rotate mr-2"></i> Refresh Manifest
                </button>
            </div>
        </form>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(420px,0.9fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Dataset Catalog</h2>
                <p class="text-sm text-gray-500">Daftar dataset yang akan masuk ke warehouse atau BI semantic layer.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Dataset</th>
                            <th class="px-5 py-3 text-left">Rows</th>
                            <th class="px-5 py-3 text-left">Watermark</th>
                            <th class="px-5 py-3 text-left">Freshness</th>
                            <th class="px-5 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($datasets as $dataset)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $dataset['code'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $dataset['name'] }} · {{ $dataset['table'] }}</div>
                                    <div class="mt-1 text-xs leading-5 text-gray-400">{{ $dataset['description'] }}</div>
                                </td>
                                <td class="px-5 py-4 font-semibold text-blue-600">{{ number_format($dataset['rows'], 0, ',', '.') }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $dataset['watermark'] }}</td>
                                <td class="px-5 py-4 text-gray-500">{{ $dataset['freshness'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass($dataset['status']) }}">{{ $dataset['status_label'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Snapshot Plan</h2>
                <p class="text-sm text-gray-500">Manifest export yang nantinya dipakai scheduler/worker.</p>
            </div>
            <div class="space-y-4 p-5">
                @foreach([
                    ['label' => 'Snapshot ID', 'value' => $snapshotPlan['snapshot_id']],
                    ['label' => 'Format', 'value' => $snapshotPlan['format']],
                    ['label' => 'Schedule', 'value' => $snapshotPlan['schedule']],
                    ['label' => 'Destination', 'value' => $snapshotPlan['destination']],
                    ['label' => 'Estimated Files', 'value' => $snapshotPlan['estimated_files']],
                    ['label' => 'Estimated Rows', 'value' => number_format($snapshotPlan['estimated_rows'], 0, ',', '.')],
                ] as $item)
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                        <div class="mt-1 break-words text-sm font-semibold text-gray-900">{{ $item['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Quality Gate</h2>
                <p class="text-sm text-gray-500">Issue ini sebaiknya bersih sebelum scheduled export production.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($qualityChecks as $check)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $check['label'] }}</div>
                            <div class="mt-1 text-sm text-gray-500">{{ $check['note'] }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold {{ $check['status'] === 'warning' ? 'text-amber-600' : 'text-green-600' }}">{{ $check['value'] }}</div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass($check['status']) }}">{{ $check['status'] === 'warning' ? 'Review' : 'Clear' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Action Item</h2>
                <p class="text-sm text-gray-500">Langkah berikutnya sebelum export fisik diaktifkan.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($actions as $index => $action)
                    <div class="flex gap-3 px-5 py-4">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-600">{{ $index + 1 }}</span>
                        <div class="text-sm leading-6 text-gray-700">{{ $action }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
