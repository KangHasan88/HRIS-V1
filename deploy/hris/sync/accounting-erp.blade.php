@extends('layouts.sidebar')

@section('title', 'Accounting & ERP')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Accounting & ERP</h1>
            <p class="mt-1 text-sm text-gray-500">Generate jurnal payroll, mapping GL/cost center, dan export payload untuk ERP/accounting.</p>
        </div>
        <a href="{{ route('payroll.run-wizard') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-list-check mr-2"></i> Proses Payroll
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Connector</div><div class="mt-1 text-xl font-semibold text-gray-900">{{ $summary['connectors'] }}</div><div class="text-xs text-gray-400">ERP/accounting</div></div>
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Aktif</div><div class="mt-1 text-xl font-semibold text-green-600">{{ $summary['active'] }}</div><div class="text-xs text-gray-400">Siap pakai</div></div>
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Journal</div><div class="mt-1 text-xl font-semibold text-blue-600">{{ $summary['journals'] }}</div><div class="text-xs text-gray-400">Batch</div></div>
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Ready</div><div class="mt-1 text-xl font-semibold text-amber-600">{{ $summary['ready'] }}</div><div class="text-xs text-gray-400">Siap posting</div></div>
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Debit</div><div class="mt-1 text-xl font-semibold text-gray-900">Rp {{ number_format($summary['debit_total'], 0, ',', '.') }}</div><div class="text-xs text-gray-400">Total</div></div>
            <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Credit</div><div class="mt-1 text-xl font-semibold text-gray-900">Rp {{ number_format($summary['credit_total'], 0, ',', '.') }}</div><div class="text-xs text-gray-400">Total</div></div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Setup Connector ERP</h2>
                <p class="text-sm text-gray-500">Mapping akun GL dan cost center dipakai saat generate jurnal payroll.</p>
            </div>
            <form method="POST" action="{{ route('integrations.accounting-erp.connectors') }}" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Semua perusahaan</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                        @endforeach
                    </select>
                    <input name="connector_code" required placeholder="Kode connector, contoh: ERP-BMP-001" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="connector_name" required placeholder="Nama connector" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="erp_system" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="generic">Generic ERP</option>
                        <option value="sap">SAP</option>
                        <option value="oracle">Oracle</option>
                        <option value="accurate">Accurate</option>
                        <option value="odoo">Odoo</option>
                        <option value="jurnal">Jurnal.id</option>
                    </select>
                    <select name="connection_type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="file_export">File export</option>
                        <option value="api_push">API push</option>
                        <option value="sftp_batch">SFTP batch</option>
                    </select>
                    <select name="auth_mode" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="api_key">API Key</option>
                        <option value="oauth2">OAuth2</option>
                        <option value="basic">Basic Auth</option>
                        <option value="sftp_key">SFTP Key</option>
                        <option value="manual">Manual upload</option>
                    </select>
                    <input name="base_url" placeholder="Base URL ERP/API" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="credential_reference" placeholder="Credential reference" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Mapping GL</h3>
                    <div class="mt-3 grid gap-3 md:grid-cols-4">
                        <input name="salary_expense_gl" placeholder="Beban gaji: {{ $glDefaults['salary_expense'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="bpjs_expense_gl" placeholder="Beban BPJS: {{ $glDefaults['bpjs_expense'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="tax_payable_gl" placeholder="Utang pajak/BPJS: {{ $glDefaults['tax_payable'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="net_payable_gl" placeholder="Utang gaji: {{ $glDefaults['net_payable'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <input name="default_cost_center" placeholder="Default cost center, contoh: HO-GA" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <select name="journal_mode" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="summary_by_cost_center">Summary per cost center</option>
                            <option value="detail_by_employee">Detail per employee</option>
                            <option value="summary_company">Summary company</option>
                        </select>
                        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="draft">Draft</option>
                            <option value="testing">Testing</option>
                            <option value="active">Active</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                </div>
                <textarea name="notes" rows="3" placeholder="Catatan integrasi ERP, format file, atau approval finance." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-plug mr-2"></i>Simpan Connector</button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-lg font-semibold text-gray-900">Generate Journal Payroll</h2>
                <p class="text-sm text-gray-500">Ambil slip published/acknowledged, group per cost center, lalu buat debit/credit seimbang.</p>
            </div>
            <form method="POST" action="{{ route('integrations.accounting-erp.journals') }}" class="mt-5 space-y-4">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <select name="accounting_erp_connector_id" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih connector</option>
                        @foreach($connectors as $connector)
                            <option value="{{ $connector->id }}">{{ $connector->connector_code }} - {{ $connector->connector_name }}</option>
                        @endforeach
                    </select>
                    <select name="company_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Ikuti connector</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select name="payroll_run_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Tanpa link payroll run</option>
                        @foreach($payrollRuns as $run)
                            <option value="{{ $run->id }}">{{ $run->run_number }} - {{ $run->period }}</option>
                        @endforeach
                    </select>
                    <input name="period" type="month" required value="{{ now()->format('Y-m') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <input name="posting_date" type="date" required value="{{ now()->format('Y-m-d') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <select name="journal_type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="payroll_accrual">Payroll accrual</option>
                        <option value="payroll_payment">Payroll payment</option>
                        <option value="tax_bpjs_accrual">Tax/BPJS accrual</option>
                    </select>
                </div>
                <textarea name="notes" rows="4" placeholder="Catatan posting, cut-off finance, atau referensi payroll." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <div class="flex justify-end">
                    <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"><i class="fas fa-file-invoice-dollar mr-2"></i>Generate Journal</button>
                </div>
            </form>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Journal Batch</h2>
            <p class="text-sm text-gray-500">Review balancing debit/credit, checksum, dan payload sebelum posting ke ERP.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Journal</th>
                        <th class="px-5 py-3">Connector</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3">Balance</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($journals as $journal)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $journal->journal_number }}</div>
                                <div class="text-xs text-gray-500">{{ $journal->journal_type }} - {{ $journal->line_count }} line</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $journal->connector?->connector_code ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ strtoupper($journal->connector?->erp_system ?? '-') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">{{ $journal->period }}</div>
                                <div class="text-xs text-gray-500">{{ $journal->posting_date?->format('d M Y') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-gray-500">D: Rp {{ number_format((float) $journal->debit_total, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-500">C: Rp {{ number_format((float) $journal->credit_total, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ strtoupper(str_replace('_', ' ', $journal->status)) }}</span>
                                <div class="mt-1 font-mono text-[11px] text-gray-400">{{ $journal->payload_checksum ? substr($journal->payload_checksum, 0, 12) : '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('integrations.accounting-erp.download', $journal) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50"><i class="fas fa-download mr-1"></i>Payload</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada journal batch.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
