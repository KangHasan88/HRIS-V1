@extends('layouts.sidebar')

@section('title', 'Integrasi Coretax')

@php
    $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Integrasi Coretax</h1>
            <p class="mt-1 text-sm text-gray-500">Command center untuk credential, template, queue, retry, submission tracker, dan rekonsiliasi Coretax.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('payroll.tax-report-packages') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fas fa-file-export mr-2"></i> Laporan Pajak
            </a>
            <a href="{{ route('payroll.coretax-connector') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-plug mr-2"></i> Connector
            </a>
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-5">
            @foreach([
                ['label' => 'Readiness', 'value' => $summary['readiness'].'%', 'color' => $summary['readiness'] >= 80 ? 'text-green-600' : 'text-amber-600', 'sub' => 'Kesiapan integrasi'],
                ['label' => 'Credential Aktif', 'value' => $summary['active_credentials'], 'color' => 'text-blue-600', 'sub' => $summary['credentials'].' total vault'],
                ['label' => 'Template Siap', 'value' => $summary['ready_templates'], 'color' => 'text-green-600', 'sub' => $summary['templates'].' total template'],
                ['label' => 'Queue Aktif', 'value' => $summary['queued'] + $summary['processing'], 'color' => 'text-amber-600', 'sub' => $summary['failed'].' failed'],
                ['label' => 'Selisih Ack', 'value' => $money($summary['difference']), 'color' => abs((float) $summary['difference']) < 1 ? 'text-green-600' : 'text-red-600', 'sub' => $summary['submissions'].' submission'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(360px,0.95fr)]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Checklist Integrasi</h2>
                    <p class="text-sm text-gray-500">Pastikan semua item aman sebelum submit data pajak ke Coretax.</p>
                </div>
                <div class="group relative">
                    <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                    <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                        Halaman ini tidak mengirim data ke API eksternal langsung. Ini memonitor kesiapan credential, template laporan pajak, retry queue, submission tracker, dan selisih acknowledgment agar tim payroll punya kontrol sebelum submit resmi.
                    </div>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($readinessChecks as $check)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $check['label'] }}</div>
                            <div class="mt-1 text-sm text-gray-500">{{ $check['note'] }}</div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">
                            {{ !empty($check['money']) ? $money($check['value']) : $check['value'].' / '.$check['target'] }}
                        </div>
                        <div class="flex items-center gap-2 md:justify-end">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ in_array($check['status'], ['Siap', 'Terkontrol'], true) ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ $check['status'] }}</span>
                            <a href="{{ route($check['route']) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Detail</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pipeline Status</h2>
                <p class="text-sm text-gray-500">Ringkasan status dari template sampai acknowledgment.</p>
            </div>
            <div class="mt-4 grid gap-3">
                @foreach([
                    ['label' => 'Report package', 'value' => $summary['packages'], 'desc' => 'Sumber data pajak bulanan', 'color' => 'bg-blue-50 text-blue-700'],
                    ['label' => 'Template export', 'value' => $summary['templates'], 'desc' => 'Payload sesuai schema Coretax', 'color' => 'bg-blue-50 text-blue-700'],
                    ['label' => 'Queued', 'value' => $summary['queued'], 'desc' => 'Menunggu worker/retry', 'color' => 'bg-amber-50 text-amber-700'],
                    ['label' => 'Processing', 'value' => $summary['processing'], 'desc' => 'Sedang diproses/tracked', 'color' => 'bg-amber-50 text-amber-700'],
                    ['label' => 'Accepted', 'value' => $summary['accepted'], 'desc' => 'Diterima acknowledgment', 'color' => 'bg-green-50 text-green-700'],
                    ['label' => 'Rejected/Partial', 'value' => $summary['rejected'] + $summary['partial'], 'desc' => 'Perlu koreksi/submit ulang', 'color' => 'bg-red-50 text-red-700'],
                ] as $item)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 p-3">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $item['label'] }}</div>
                            <div class="text-xs text-gray-500">{{ $item['desc'] }}</div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $item['color'] }}">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Credential Vault</h2>
                <p class="text-sm text-gray-500">Credential aktif dan status endpoint terakhir.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($settings as $setting)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $setting->company?->code ?? 'All Company' }} · {{ ucfirst($setting->environment) }}</div>
                            <div class="text-sm text-gray-500">{{ $setting->base_url }}</div>
                            <div class="mt-1 font-mono text-xs text-gray-400">{{ $setting->client_id }} · {{ $setting->secret_mask }}</div>
                        </div>
                        <div class="text-right">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $setting->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $setting->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            <div class="mt-1 text-xs text-gray-500">{{ $setting->last_status ?: '-' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada credential Coretax.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Template Export Terbaru</h2>
                <p class="text-sm text-gray-500">Payload yang siap masuk queue Coretax.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($templates as $template)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $template->template_number }}</div>
                            <div class="text-sm text-gray-500">{{ $template->package?->company?->code ?? 'All' }} · {{ $template->package?->period ?? '-' }} · {{ strtoupper($template->format) }}</div>
                            <div class="mt-1 font-mono text-xs text-gray-400">{{ $template->checksum ? substr($template->checksum, 0, 18).'...' : '-' }}</div>
                        </div>
                        <div class="text-right">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($template->status) }}</span>
                            <div class="mt-1 text-xs text-gray-500">{{ count($template->payload_snapshot ?? []) }} rows</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-500">Belum ada template export pajak.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Retry Queue</h2>
            <p class="text-sm text-gray-500">Request Coretax yang sedang menunggu retry, processing, success, atau failed.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Request</th>
                        <th class="px-5 py-3">Credential</th>
                        <th class="px-5 py-3">Template</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Attempt</th>
                        <th class="px-5 py-3">Next Retry</th>
                        <th class="px-5 py-3">Response</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($retryLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst($log->request_type)) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $log->setting?->company?->code ?? 'All' }} · {{ ucfirst($log->setting?->environment ?? '-') }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $log->template?->template_number ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $log->status === 'failed' ? 'bg-red-50 text-red-700' : ($log->status === 'success' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($log->status) }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $log->attempt }} / {{ $log->max_retry }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $log->next_retry_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $log->response_message ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada retry queue Coretax.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Submission Tracker</h2>
            <p class="text-sm text-gray-500">Rekonsiliasi acknowledgment Coretax terhadap template yang dikirim.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Submission</th>
                        <th class="px-5 py-3">Template</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Rows</th>
                        <th class="px-5 py-3">Template PPh</th>
                        <th class="px-5 py-3">Ack PPh</th>
                        <th class="px-5 py-3">Selisih</th>
                        <th class="px-5 py-3">Last Check</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($submissions as $submission)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $submission->submission_number }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $submission->template?->template_number ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $submission->submission_status === 'accepted' ? 'bg-green-50 text-green-700' : ($submission->submission_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">{{ ucfirst($submission->submission_status) }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $submission->accepted_rows }} accepted / {{ $submission->rejected_rows }} rejected</td>
                            <td class="px-5 py-4 text-gray-600">{{ $money($submission->template_total_pph21) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $money($submission->acknowledged_total_pph21) }}</td>
                            <td class="px-5 py-4 font-semibold {{ abs((float) $submission->difference_amount) < 1 ? 'text-green-600' : 'text-red-600' }}">{{ $money($submission->difference_amount) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $submission->last_checked_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada submission tracker Coretax.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
