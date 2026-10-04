<?php

namespace App\Http\Controllers;

use App\Models\AccountingErpConnector;
use App\Models\Company;
use App\Models\PayrollAccountingJournalBatch;
use App\Models\PayrollAccountingJournalLine;
use App\Models\PayrollPayslip;
use App\Models\PayrollRun;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountingErpController extends Controller
{
    public function index(): View
    {
        $this->authorizeAccess();

        $connectors = AccountingErpConnector::with(['company', 'journals'])->latest()->limit(80)->get();
        $journals = PayrollAccountingJournalBatch::with(['company', 'connector', 'payrollRun', 'lines'])->latest()->limit(80)->get();

        return view('integrations.accounting-erp', [
            'active' => 'accounting-erp',
            'companies' => Company::orderBy('code')->get(),
            'payrollRuns' => PayrollRun::latest()->limit(80)->get(),
            'connectors' => $connectors,
            'journals' => $journals,
            'summary' => [
                'connectors' => $connectors->count(),
                'active' => $connectors->where('status', 'active')->count(),
                'journals' => $journals->count(),
                'ready' => $journals->where('status', 'ready_to_post')->count(),
                'debit_total' => $journals->sum(fn ($journal) => (float) $journal->debit_total),
                'credit_total' => $journals->sum(fn ($journal) => (float) $journal->credit_total),
            ],
            'glDefaults' => $this->defaultGlMapping(),
        ]);
    }

    public function storeConnector(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'connector_code' => ['required', 'string', 'max:100', 'unique:accounting_erp_connectors,connector_code'],
            'connector_name' => ['required', 'string', 'max:255'],
            'erp_system' => ['required', Rule::in(['generic', 'sap', 'oracle', 'accurate', 'odoo', 'jurnal'])],
            'connection_type' => ['required', Rule::in(['file_export', 'api_push', 'sftp_batch'])],
            'base_url' => ['nullable', 'url', 'max:255'],
            'auth_mode' => ['required', Rule::in(['api_key', 'oauth2', 'basic', 'sftp_key', 'manual'])],
            'credential_reference' => ['nullable', 'string', 'max:255'],
            'salary_expense_gl' => ['nullable', 'string', 'max:80'],
            'bpjs_expense_gl' => ['nullable', 'string', 'max:80'],
            'tax_payable_gl' => ['nullable', 'string', 'max:80'],
            'net_payable_gl' => ['nullable', 'string', 'max:80'],
            'default_cost_center' => ['nullable', 'string', 'max:120'],
            'journal_mode' => ['required', Rule::in(['summary_by_cost_center', 'detail_by_employee', 'summary_company'])],
            'status' => ['required', Rule::in(['draft', 'testing', 'active', 'disabled'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $connector = AccountingErpConnector::create([
            'company_id' => $data['company_id'] ?? null,
            'connector_code' => strtoupper($data['connector_code']),
            'connector_name' => $data['connector_name'],
            'erp_system' => $data['erp_system'],
            'connection_type' => $data['connection_type'],
            'base_url' => $data['base_url'] ?? null,
            'auth_mode' => $data['auth_mode'],
            'credential_reference' => $data['credential_reference'] ?? null,
            'gl_mapping' => [
                ...$this->defaultGlMapping(),
                'salary_expense' => $data['salary_expense_gl'] ?: $this->defaultGlMapping()['salary_expense'],
                'bpjs_expense' => $data['bpjs_expense_gl'] ?: $this->defaultGlMapping()['bpjs_expense'],
                'tax_payable' => $data['tax_payable_gl'] ?: $this->defaultGlMapping()['tax_payable'],
                'net_payable' => $data['net_payable_gl'] ?: $this->defaultGlMapping()['net_payable'],
            ],
            'cost_center_mapping' => [
                'default' => $data['default_cost_center'] ?: 'HO-GA',
                'source' => 'employee.cost_center',
            ],
            'journal_mode' => $data['journal_mode'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('accounting_erp_connector.created', "Connector ERP {$connector->connector_code} dibuat.", $connector, [], $connector->toArray(), $request);

        return redirect()->route('integrations.accounting-erp')->with('success', 'Connector accounting/ERP berhasil disimpan.');
    }

    public function generateJournal(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'accounting_erp_connector_id' => ['required', 'exists:accounting_erp_connectors,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'payroll_run_id' => ['nullable', 'exists:payroll_runs,id'],
            'period' => ['required', 'date_format:Y-m'],
            'posting_date' => ['required', 'date'],
            'journal_type' => ['required', Rule::in(['payroll_accrual', 'payroll_payment', 'tax_bpjs_accrual'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $connector = AccountingErpConnector::findOrFail($data['accounting_erp_connector_id']);
        $gl = array_merge($this->defaultGlMapping(), $connector->gl_mapping ?: []);

        $journal = DB::transaction(function () use ($data, $connector, $gl, $request): PayrollAccountingJournalBatch {
            $company = isset($data['company_id']) ? Company::find($data['company_id']) : $connector->company;
            $payslips = PayrollPayslip::with('employee')
                ->where('period', $data['period'])
                ->whereIn('status', ['published', 'acknowledged'])
                ->when($data['company_id'] ?? $connector->company_id, fn ($query, $companyId) => $query->where('company_id', $companyId))
                ->get();

            $journal = PayrollAccountingJournalBatch::create([
                'accounting_erp_connector_id' => $connector->id,
                'company_id' => $data['company_id'] ?? $connector->company_id,
                'payroll_run_id' => $data['payroll_run_id'] ?? null,
                'journal_number' => 'JRN-PAY-'.str_replace('-', '', $data['period']).'-'.strtoupper($company?->code ?? 'ALL').'-'.now()->format('His'),
                'period' => $data['period'],
                'posting_date' => $data['posting_date'],
                'journal_type' => $data['journal_type'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $groups = $payslips->groupBy(fn ($payslip) => $payslip->employee?->cost_center ?: ($connector->cost_center_mapping['default'] ?? 'HO-GA'));
            $payload = [];
            $lineNo = 1;
            $debitTotal = 0;
            $creditTotal = 0;

            foreach ($groups as $costCenter => $rows) {
                $gross = (float) $rows->sum(fn ($payslip) => (float) $payslip->gross_pay);
                $deduction = (float) $rows->sum(fn ($payslip) => (float) $payslip->total_deduction);
                $net = (float) $rows->sum(fn ($payslip) => (float) $payslip->net_pay);

                $lines = [
                    ['account' => $gl['salary_expense'], 'name' => 'Beban gaji', 'dc' => 'debit', 'amount' => $gross],
                    ['account' => $gl['tax_payable'], 'name' => 'Utang pajak/BPJS payroll', 'dc' => 'credit', 'amount' => $deduction],
                    ['account' => $gl['net_payable'], 'name' => 'Utang gaji bersih', 'dc' => 'credit', 'amount' => $net],
                ];

                foreach ($lines as $line) {
                    if ($line['amount'] <= 0) {
                        continue;
                    }

                    PayrollAccountingJournalLine::create([
                        'payroll_accounting_journal_batch_id' => $journal->id,
                        'line_number' => $journal->journal_number.'-'.str_pad((string) $lineNo, 3, '0', STR_PAD_LEFT),
                        'cost_center_code' => (string) $costCenter,
                        'gl_account' => $line['account'],
                        'gl_name' => $line['name'],
                        'debit_credit' => $line['dc'],
                        'amount' => $line['amount'],
                        'employee_scope' => $rows->count().' employee',
                        'description' => $line['name'].' periode '.$data['period'].' cost center '.$costCenter,
                        'source_snapshot' => [
                            'period' => $data['period'],
                            'employee_count' => $rows->count(),
                            'gross' => $gross,
                            'deduction' => $deduction,
                            'net' => $net,
                        ],
                    ]);

                    $payload[] = [
                        'line_number' => $lineNo,
                        'cost_center' => (string) $costCenter,
                        'gl_account' => $line['account'],
                        'gl_name' => $line['name'],
                        'debit_credit' => $line['dc'],
                        'amount' => $line['amount'],
                        'currency' => 'IDR',
                        'description' => $line['name'].' '.$data['period'],
                    ];

                    $line['dc'] === 'debit' ? $debitTotal += $line['amount'] : $creditTotal += $line['amount'];
                    $lineNo++;
                }
            }

            $journal->forceFill([
                'line_count' => count($payload),
                'debit_total' => $debitTotal,
                'credit_total' => $creditTotal,
                'status' => abs($debitTotal - $creditTotal) < 0.01 && count($payload) > 0 ? 'ready_to_post' : 'needs_review',
                'export_payload' => [
                    'connector' => $connector->connector_code,
                    'journal_number' => $journal->journal_number,
                    'period' => $data['period'],
                    'posting_date' => $data['posting_date'],
                    'mode' => $connector->journal_mode,
                    'lines' => $payload,
                ],
                'payload_checksum' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE)),
            ])->save();

            AuditLogger::record('payroll_accounting_journal.generated', "Jurnal payroll {$journal->journal_number} dibuat.", $journal, [], $journal->fresh()->toArray(), $request);

            return $journal;
        });

        return redirect()->route('integrations.accounting-erp')->with('success', "Jurnal {$journal->journal_number} berhasil dibuat.");
    }

    public function download(PayrollAccountingJournalBatch $journal): Response
    {
        $this->authorizeAccess();

        return response(json_encode($journal->export_payload ?: [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.strtolower($journal->journal_number).'.json"',
        ]);
    }

    private function authorizeAccess(): void
    {
        abort_unless(Gate::allows('manage payroll settings') || Gate::allows('manage modules') || Gate::allows('view delivery progress'), 403);
    }

    private function defaultGlMapping(): array
    {
        return [
            'salary_expense' => '6100-001',
            'bpjs_expense' => '6100-021',
            'tax_payable' => '2100-211',
            'net_payable' => '2100-101',
        ];
    }
}
