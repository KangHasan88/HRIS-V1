<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\EmployeeBankAccount;
use App\Models\PayrollDisbursementApproval;
use App\Models\PayrollDisbursementBatch;
use App\Models\PayrollDisbursementItem;
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

class PayrollDisbursementController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $batches = PayrollDisbursementBatch::with(['company', 'payrollRun', 'items', 'approvals'])
            ->latest()
            ->limit(80)
            ->get();

        return view('payroll.disbursements', [
            'active' => 'payroll-disbursements',
            'companies' => Company::orderBy('code')->get(),
            'payrollRuns' => PayrollRun::latest()->limit(50)->get(),
            'batches' => $batches,
            'summary' => [
                'batches' => PayrollDisbursementBatch::count(),
                'pending' => PayrollDisbursementBatch::whereIn('status', ['draft', 'pending_approval'])->count(),
                'approved' => PayrollDisbursementBatch::where('status', 'approved')->count(),
                'total' => PayrollDisbursementBatch::sum('total_amount'),
            ],
            'bankChannels' => $this->bankChannels(),
            'fileFormats' => $this->fileFormats(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'payroll_run_id' => ['nullable', 'exists:payroll_runs,id'],
            'period' => ['required', 'date_format:Y-m'],
            'bank_channel' => ['required', Rule::in(array_keys($this->bankChannels()))],
            'source_account' => ['nullable', 'string', 'max:120'],
            'scheduled_payment_date' => ['required', 'date'],
            'file_format' => ['required', Rule::in(array_keys($this->fileFormats()))],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $batch = DB::transaction(function () use ($data, $request): PayrollDisbursementBatch {
            $company = $data['company_id'] ? Company::find($data['company_id']) : null;
            $payslips = PayrollPayslip::with('employee')
                ->where('period', $data['period'])
                ->where('status', 'published')
                ->when($data['company_id'] ?? null, fn ($query, $companyId) => $query->where('company_id', $companyId))
                ->get();

            $batch = PayrollDisbursementBatch::create([
                ...$data,
                'batch_number' => 'DISB-' . str_replace('-', '', $data['period']) . '-' . strtoupper($company?->code ?? 'ALL') . '-' . now()->format('His'),
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $payload = [];
            $total = 0;
            foreach ($payslips as $payslip) {
                $employee = $payslip->employee;
                $bank = $employee ? EmployeeBankAccount::where('employee_profile_id', $employee->id)->where('is_primary', true)->first() : null;
                $status = $bank ? 'ready' : 'blocked';
                $message = $bank ? null : 'Rekening utama belum tersedia.';
                $amount = (float) $payslip->net_pay;
                $total += $status === 'ready' ? $amount : 0;

                PayrollDisbursementItem::create([
                    'payroll_disbursement_batch_id' => $batch->id,
                    'payroll_payslip_id' => $payslip->id,
                    'employee_profile_id' => $employee?->id,
                    'employee_number' => $employee?->employee_number,
                    'employee_name' => $employee?->full_name,
                    'bank_name' => $bank?->bank_name,
                    'bank_account_number' => $bank?->account_number,
                    'bank_account_holder' => $bank?->account_holder_name,
                    'amount' => $amount,
                    'status' => $status,
                    'validation_message' => $message,
                ]);

                $payload[] = [
                    'transaction_ref' => $this->transactionReference($data['period'], $employee?->employee_number),
                    'employee_number' => $employee?->employee_number,
                    'employee_name' => $employee?->full_name,
                    'bank_name' => $bank?->bank_name,
                    'account_number' => $bank?->account_number,
                    'account_holder' => $bank?->account_holder_name,
                    'amount' => $amount,
                    'currency' => 'IDR',
                    'payment_date' => $data['scheduled_payment_date'],
                    'remark' => 'Payroll '.$data['period'],
                    'status' => $status,
                ];
            }

            PayrollDisbursementApproval::create([
                'payroll_disbursement_batch_id' => $batch->id,
                'approval_step' => 'payroll_review',
                'approver_role' => 'Payroll Admin',
            ]);
            PayrollDisbursementApproval::create([
                'payroll_disbursement_batch_id' => $batch->id,
                'approval_step' => 'finance_release',
                'approver_role' => 'Finance Approver',
            ]);

            $blocked = PayrollDisbursementItem::where('payroll_disbursement_batch_id', $batch->id)->where('status', 'blocked')->count();
            $batch->forceFill([
                'item_count' => count($payload),
                'total_amount' => $total,
                'status' => $blocked > 0 ? 'draft' : 'pending_approval',
                'export_payload' => $payload,
                'file_checksum' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE)),
            ])->save();

            AuditLogger::record('payroll_disbursement.created', "Batch pencairan {$batch->batch_number} dibuat.", $batch, [], $batch->fresh()->toArray(), $request);

            return $batch;
        });

        return redirect()->route('payroll.disbursements')->with('success', "Batch {$batch->batch_number} berhasil dibuat.");
    }

    public function approve(Request $request, PayrollDisbursementBatch $batch): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:800'],
        ]);

        $approval = $batch->approvals()->where('decision', 'pending')->orderBy('id')->firstOrFail();
        $approval->forceFill([
            'decision' => $data['decision'],
            'approver_user_id' => auth()->id(),
            'decided_at' => now(),
            'notes' => $data['notes'] ?? null,
        ])->save();

        $batch->forceFill([
            'status' => $data['decision'] === 'rejected'
                ? 'rejected'
                : ($batch->approvals()->where('decision', 'pending')->exists() ? 'pending_approval' : 'approved'),
        ])->save();

        AuditLogger::record('payroll_disbursement.approved', "Approval {$approval->approval_step} untuk {$batch->batch_number}: {$data['decision']}.", $batch, [], [
            'approval_step' => $approval->approval_step,
            'decision' => $data['decision'],
        ], $request);

        return redirect()->route('payroll.disbursements')->with('success', 'Approval pencairan berhasil diperbarui.');
    }

    public function download(PayrollDisbursementBatch $batch): Response
    {
        Gate::authorize('manage payroll settings');

        $rows = $batch->items()->orderBy('employee_number')->get();
        $payload = $rows->map(fn ($item) => [
            'transaction_ref' => $this->transactionReference((string) $batch->period, $item->employee_number),
            'employee_number' => $item->employee_number,
            'employee_name' => $item->employee_name,
            'bank_name' => $item->bank_name,
            'bank_account_number' => $item->bank_account_number,
            'bank_account_holder' => $item->bank_account_holder,
            'amount' => (float) $item->amount,
            'currency' => 'IDR',
            'payment_date' => $batch->scheduled_payment_date?->format('Y-m-d'),
            'remark' => 'Payroll '.$batch->period,
            'status' => $item->status,
        ]);

        if (in_array($batch->file_format, ['json', 'h2h_json'], true)) {
            return response(json_encode([
                'batch_number' => $batch->batch_number,
                'bank_channel' => $batch->bank_channel,
                'source_account' => $batch->source_account,
                'checksum' => $batch->file_checksum,
                'release_status' => $batch->status,
                'transactions' => $payload,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . strtolower($batch->batch_number) . '.json"',
            ]);
        }

        if ($batch->file_format === 'bank_fixed_width') {
            $body = '';
            foreach ($payload as $row) {
                $body .= str_pad((string) $row['transaction_ref'], 24)
                    .str_pad((string) $row['bank_account_number'], 24)
                    .str_pad(substr((string) $row['bank_account_holder'], 0, 40), 40)
                    .str_pad((string) round(((float) $row['amount']) * 100), 16, '0', STR_PAD_LEFT)
                    .str_pad((string) $row['remark'], 32)
                    .PHP_EOL;
            }

            return response($body, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . strtolower($batch->batch_number) . '.txt"',
            ]);
        }

        $csv = "transaction_ref,employee_number,employee_name,bank_name,account_number,account_holder,amount,currency,payment_date,remark,status\n";
        foreach ($payload as $row) {
            $csv .= implode(',', [
                $row['transaction_ref'],
                $row['employee_number'],
                '"' . str_replace('"', '""', (string) $row['employee_name']) . '"',
                $row['bank_name'],
                $row['bank_account_number'],
                '"' . str_replace('"', '""', (string) $row['bank_account_holder']) . '"',
                number_format((float) $row['amount'], 2, '.', ''),
                $row['currency'],
                $row['payment_date'],
                '"' . str_replace('"', '""', (string) $row['remark']) . '"',
                $row['status'],
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . strtolower($batch->batch_number) . '.csv"',
        ]);
    }

    private function bankChannels(): array
    {
        return [
            'bank_transfer' => 'Transfer manual / upload bank',
            'bca_klikbisnis' => 'BCA KlikBisnis',
            'mandiri_mcm' => 'Mandiri MCM',
            'bni_direct' => 'BNI Direct',
            'bri_cms' => 'BRI CMS',
            'host_to_host' => 'Host-to-host bank API',
        ];
    }

    private function fileFormats(): array
    {
        return [
            'csv' => 'CSV payroll transfer',
            'bank_fixed_width' => 'Bank fixed width TXT',
            'h2h_json' => 'H2H JSON payload',
            'json' => 'JSON audit payload',
        ];
    }

    private function transactionReference(string $period, ?string $employeeNumber): string
    {
        return 'PAY'.str_replace('-', '', $period).'-'.strtoupper((string) $employeeNumber);
    }
}
