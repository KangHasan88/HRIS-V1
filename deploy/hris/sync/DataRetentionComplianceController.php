<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DataLegalHoldCase;
use App\Models\DataRetentionExecutionLog;
use App\Models\DataRetentionPolicy;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DataRetentionComplianceController extends Controller
{
    public function index(): View
    {
        $this->authorizeAccess();

        $policies = DataRetentionPolicy::with('company')->latest()->limit(60)->get();
        $holds = DataLegalHoldCase::with(['company', 'policy'])->latest()->limit(60)->get();
        $executions = DataRetentionExecutionLog::with(['policy', 'legalHold'])->latest()->limit(60)->get();

        return view('settings.data-retention-compliance', [
            'active' => 'data-retention-compliance',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'policies' => $policies,
            'holds' => $holds,
            'executions' => $executions,
            'domains' => $this->domains(),
            'summary' => [
                'policies' => $policies->count(),
                'active_policies' => $policies->where('status', 'active')->count(),
                'legal_holds' => $holds->where('status', 'active')->count(),
                'pending_approval' => $holds->where('approval_status', 'pending')->count(),
                'executions' => $executions->count(),
                'blocked' => $executions->where('status', 'blocked_by_legal_hold')->count(),
            ],
        ]);
    }

    public function storePolicy(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'policy_code' => ['required', 'string', 'max:100', 'unique:data_retention_policies,policy_code'],
            'policy_name' => ['required', 'string', 'max:255'],
            'data_domain' => ['required', Rule::in(array_keys($this->domains()))],
            'retention_years' => ['required', 'integer', 'min:1', 'max:30'],
            'archive_strategy' => ['required', Rule::in(['cold_storage', 'encrypted_archive', 'tenant_export', 'keep_online'])],
            'disposal_action' => ['required', Rule::in(['anonymize', 'delete', 'archive_only', 'manual_review'])],
            'owner_role' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['draft', 'active', 'disabled'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $policy = DataRetentionPolicy::create([
            ...$data,
            'policy_code' => strtoupper($data['policy_code']),
            'requires_approval' => $request->boolean('requires_approval'),
            'legal_hold_override' => $request->boolean('legal_hold_override'),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('data_retention_policy.created', "Policy retensi {$policy->policy_code} dibuat.", $policy, [], $policy->toArray(), $request);

        return redirect()->route('settings.data-retention-compliance')->with('success', 'Policy retensi berhasil disimpan.');
    }

    public function storeLegalHold(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'data_retention_policy_id' => ['nullable', 'exists:data_retention_policies,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'case_number' => ['required', 'string', 'max:100', 'unique:data_legal_hold_cases,case_number'],
            'case_title' => ['required', 'string', 'max:255'],
            'data_domain' => ['required', Rule::in(array_keys($this->domains()))],
            'scope_reference' => ['nullable', 'string', 'max:160'],
            'reason_category' => ['required', Rule::in(['legal_dispute', 'audit', 'investigation', 'regulator_request', 'employee_relation'])],
            'hold_start_date' => ['required', 'date'],
            'hold_end_date' => ['nullable', 'date', 'after_or_equal:hold_start_date'],
            'custodian_name' => ['nullable', 'string', 'max:255'],
            'approval_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'status' => ['required', Rule::in(['active', 'released', 'expired'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $hold = DataLegalHoldCase::create([
            ...$data,
            'case_number' => strtoupper($data['case_number']),
            'evidence_snapshot' => [
                'created_by' => auth()->user()?->email,
                'reason' => $data['reason_category'],
                'scope' => $data['scope_reference'] ?? 'global',
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('data_legal_hold.created', "Legal hold {$hold->case_number} dibuat.", $hold, [], $hold->toArray(), $request);

        return redirect()->route('settings.data-retention-compliance')->with('success', 'Legal hold berhasil disimpan.');
    }

    public function storeExecution(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'data_retention_policy_id' => ['nullable', 'exists:data_retention_policies,id'],
            'data_legal_hold_case_id' => ['nullable', 'exists:data_legal_hold_cases,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'execution_number' => ['required', 'string', 'max:100', 'unique:data_retention_execution_logs,execution_number'],
            'data_domain' => ['required', Rule::in(array_keys($this->domains()))],
            'cutoff_date' => ['required', 'date'],
            'action_type' => ['required', Rule::in(['archive', 'anonymize', 'delete', 'legal_hold_review'])],
            'records_scanned' => ['required', 'integer', 'min:0'],
            'records_affected' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['planned', 'running', 'completed', 'failed', 'blocked_by_legal_hold'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $execution = DataRetentionExecutionLog::create([
            ...$data,
            'execution_number' => strtoupper($data['execution_number']),
            'started_at' => in_array($data['status'], ['running', 'completed', 'failed'], true) ? now() : null,
            'completed_at' => $data['status'] === 'completed' ? now() : null,
            'result_snapshot' => [
                'records_scanned' => (int) $data['records_scanned'],
                'records_affected' => (int) $data['records_affected'],
                'blocked_by_hold' => $data['status'] === 'blocked_by_legal_hold',
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('data_retention_execution.recorded', "Eksekusi retensi {$execution->execution_number} dicatat.", $execution, [], $execution->toArray(), $request);

        return redirect()->route('settings.data-retention-compliance')->with('success', 'Eksekusi retensi berhasil dicatat.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(Gate::allows('manage modules') || Gate::allows('manage users') || Gate::allows('view delivery progress'), 403);
    }

    private function domains(): array
    {
        return [
            'employee_master' => 'Data karyawan',
            'payroll' => 'Payroll dan pajak',
            'attendance' => 'Absensi dan lokasi',
            'recruitment' => 'Rekrutmen dan kandidat',
            'documents' => 'Dokumen HR',
            'ess' => 'ESS request',
        ];
    }
}
