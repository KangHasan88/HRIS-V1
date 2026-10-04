@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Coretax Connector</h1>
            <p class="mt-1 text-sm text-gray-500">Credential vault, kesiapan endpoint, dan retry queue untuk integrasi Coretax.</p>
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
        <div class="grid gap-5 md:grid-cols-4 xl:grid-cols-8">
            @foreach([
                ['label' => 'Credential', 'value' => $summary['settings'], 'color' => 'text-blue-600'],
                ['label' => 'Aktif', 'value' => $summary['active'], 'color' => 'text-green-600'],
                ['label' => 'Queued', 'value' => $summary['queued'], 'color' => 'text-yellow-600'],
                ['label' => 'Failed', 'value' => $summary['failed'], 'color' => 'text-red-600'],
                ['label' => 'Submit', 'value' => $summary['submitted'], 'color' => 'text-blue-600'],
                ['label' => 'Accepted', 'value' => $summary['accepted'], 'color' => 'text-green-600'],
                ['label' => 'Rejected', 'value' => $summary['rejected'], 'color' => 'text-red-600'],
                ['label' => 'Selisih', 'value' => 'IDR ' . number_format($summary['difference'], 0, ',', '.'), 'color' => 'text-yellow-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Credential Vault</h2>
                <p class="text-sm text-gray-500">Secret disimpan sebagai hash dan hanya ditampilkan dalam bentuk masking.</p>
            </div>
            <form action="{{ route('payroll.coretax-connector.settings') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                    @endforeach
                </select>
                <select name="environment" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="sandbox">Sandbox</option>
                    <option value="production">Production</option>
                </select>
                <input name="base_url" required type="url" placeholder="https://api.coretax.example" class="md:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="client_id" required placeholder="Client ID" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="client_secret" required type="password" placeholder="Client secret" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="timeout_seconds" required type="number" min="5" max="120" value="30" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input name="max_retry" required type="number" min="0" max="10" value="3" class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Aktif
                </label>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-key"></i>
                        Simpan Vault
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Queue Template</h2>
                <p class="text-sm text-gray-500">Masukkan template ekspor ke retry queue tanpa mengirim ke API eksternal dulu.</p>
            </div>
            <form action="{{ route('payroll.coretax-connector.queue') }}" method="POST" class="mt-5 grid gap-4">
                @csrf
                <select name="coretax_connector_setting_id" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih credential</option>
                    @foreach($settings as $setting)
                        <option value="{{ $setting->id }}">{{ $setting->company?->code ?? 'All' }} - {{ ucfirst($setting->environment) }} - {{ $setting->client_id }}</option>
                    @endforeach
                </select>
                <select name="tax_monthly_export_template_id" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Pilih template</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->template_number }} - {{ $template->schema_version }} - {{ strtoupper($template->format) }}</option>
                    @endforeach
                </select>
                <select name="request_type" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="validate_template">Validate template</option>
                    <option value="submit_template">Submit template</option>
                    <option value="status_check">Status check</option>
                </select>
                <div class="flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i class="fas fa-clock-rotate-left"></i>
                        Masukkan Queue
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Credential Aktif</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Company</th>
                        <th class="px-5 py-3">Env</th>
                        <th class="px-5 py-3">Base URL</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Secret</th>
                        <th class="px-5 py-3">Retry</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($settings as $setting)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">{{ $setting->company?->code ?? 'All' }}</td>
                            <td class="px-5 py-4">{{ ucfirst($setting->environment) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $setting->base_url }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $setting->client_id }}</td>
                            <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $setting->secret_mask }}</td>
                            <td class="px-5 py-4">{{ $setting->max_retry }}x / {{ $setting->timeout_seconds }}s</td>
                            <td class="px-5 py-4">{{ $setting->last_status ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada credential Coretax.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Retry Log</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Request</th>
                        <th class="px-5 py-3">Template</th>
                        <th class="px-5 py-3">Endpoint</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Attempt</th>
                        <th class="px-5 py-3">Next Retry</th>
                        <th class="px-5 py-3">Checksum</th>
                        <th class="px-5 py-3">Tracker</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($retryLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst($log->request_type)) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $log->template?->template_number ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $log->endpoint }}</td>
                            <td class="px-5 py-4">{{ ucfirst($log->status) }}</td>
                            <td class="px-5 py-4">{{ $log->attempt }} / {{ $log->max_retry }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $log->next_retry_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $log->payload_checksum ? substr($log->payload_checksum, 0, 16) . '...' : '-' }}</td>
                            <td class="px-5 py-4">
                                <form action="{{ route('payroll.coretax-connector.track', $log) }}" method="POST" class="grid min-w-[460px] gap-2 md:grid-cols-5">
                                    @csrf
                                    <select name="submission_status" class="rounded-lg border border-gray-300 px-2 py-2 text-xs">
                                        <option value="processing">Processing</option>
                                        <option value="accepted">Accepted</option>
                                        <option value="partial">Partial</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                    <input name="accepted_rows" type="number" min="0" value="{{ count($log->template?->payload_snapshot ?? []) }}" class="rounded-lg border border-gray-300 px-2 py-2 text-xs">
                                    <input name="rejected_rows" type="number" min="0" value="0" class="rounded-lg border border-gray-300 px-2 py-2 text-xs">
                                    <input name="acknowledged_total_pph21" type="number" min="0" step="1" value="{{ collect($log->template?->payload_snapshot ?? [])->sum('pph21') }}" class="rounded-lg border border-gray-300 px-2 py-2 text-xs">
                                    <button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Track</button>
                                    <input name="response_message" placeholder="Response message" class="md:col-span-5 rounded-lg border border-gray-300 px-2 py-2 text-xs">
                                </form>
                                <div class="mt-2 text-xs text-gray-500">{{ $log->submissions->count() }} tracker</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada retry log Coretax.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Dashboard Rekonsiliasi Coretax</h2>
            <p class="text-sm text-gray-500">Pantau status submit, accepted/rejected rows, dan selisih PPh 21 antara template dengan acknowledgment.</p>
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
                            <td class="px-5 py-4 text-gray-600">{{ $submission->template?->template_number }}</td>
                            <td class="px-5 py-4">{{ ucfirst($submission->submission_status) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $submission->accepted_rows }} accepted / {{ $submission->rejected_rows }} rejected</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($submission->template_total_pph21, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">IDR {{ number_format($submission->acknowledged_total_pph21, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 font-semibold {{ (float) $submission->difference_amount === 0.0 ? 'text-green-600' : 'text-red-600' }}">IDR {{ number_format($submission->difference_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $submission->last_checked_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada tracker submit Coretax.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
