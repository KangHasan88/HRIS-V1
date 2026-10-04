<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PlatformDataConsent;
use App\Models\PlatformDataPrivacyPolicy;
use App\Models\PlatformDataRetentionJob;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformDataPrivacyController extends Controller
{
    public function index(): View
    {
        $this->authorizeAccess();

        $policies = PlatformDataPrivacyPolicy::with('company')->latest()->limit(60)->get();
        $consents = PlatformDataConsent::with(['company', 'policy'])->latest()->limit(60)->get();
        $retentionJobs = PlatformDataRetentionJob::with(['company', 'policy'])->latest()->limit(60)->get();

        return view('settings.data-privacy', [
            'active' => 'data-privacy',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'policies' => $policies,
            'consents' => $consents,
            'retentionJobs' => $retentionJobs,
            'summary' => [
                'policies' => $policies->count(),
                'active' => $policies->where('status', 'active')->count(),
                'consent_required' => $policies->where('consent_required', true)->count(),
                'consents' => $consents->count(),
                'retention_jobs' => $retentionJobs->count(),
                'legal_hold' => $retentionJobs->where('legal_hold', true)->count(),
            ],
            'fieldCatalog' => $this->fieldCatalog(),
            'roleCatalog' => ['Super Admin', 'HR Admin', 'Payroll Admin', 'Manager', 'Employee', 'Auditor'],
        ]);
    }

    public function storePolicy(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'policy_code' => ['required', 'string', 'max:100', 'unique:platform_data_privacy_policies,policy_code'],
            'policy_name' => ['required', 'string', 'max:255'],
            'data_domain' => ['required', Rule::in(array_keys($this->fieldCatalog()))],
            'masked_fields' => ['nullable', 'array'],
            'masked_fields.*' => ['string', 'max:80'],
            'restricted_roles' => ['nullable', 'array'],
            'restricted_roles.*' => ['string', 'max:80'],
            'masking_mode' => ['required', Rule::in(['partial', 'full', 'hash_only', 'role_based'])],
            'retention_years' => ['required', 'integer', 'min:1', 'max:30'],
            'legal_basis' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'active', 'disabled'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $policy = PlatformDataPrivacyPolicy::create([
            ...$data,
            'policy_code' => strtoupper($data['policy_code']),
            'masked_fields' => array_values($data['masked_fields'] ?? []),
            'restricted_roles' => array_values($data['restricted_roles'] ?? []),
            'consent_required' => $request->boolean('consent_required'),
            'legal_hold_enabled' => $request->boolean('legal_hold_enabled'),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('platform_privacy_policy.created', "Policy privasi {$policy->policy_code} dibuat.", $policy, [], $policy->toArray(), $request);

        return redirect()->route('settings.data-privacy')->with('success', 'Policy privasi data berhasil disimpan.');
    }

    public function storeConsent(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'platform_data_privacy_policy_id' => ['nullable', 'exists:platform_data_privacy_policies,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'subject_type' => ['required', Rule::in(['employee', 'candidate', 'vendor_worker', 'admin_user'])],
            'subject_reference' => ['required', 'string', 'max:120'],
            'subject_name' => ['nullable', 'string', 'max:255'],
            'consent_purpose' => ['required', 'string', 'max:255'],
            'consent_channel' => ['required', Rule::in(['web', 'mobile_webview', 'paper', 'email', 'api'])],
            'status' => ['required', Rule::in(['granted', 'revoked', 'expired', 'pending'])],
            'granted_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $consent = PlatformDataConsent::create([
            ...$data,
            'granted_at' => $data['granted_at'] ?? ($data['status'] === 'granted' ? now() : null),
            'revoked_at' => $data['status'] === 'revoked' ? now() : null,
            'evidence_snapshot' => [
                'channel' => $data['consent_channel'],
                'recorded_by' => auth()->user()?->email,
                'ip' => $request->ip(),
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('platform_data_consent.recorded', "Consent {$consent->subject_reference} dicatat.", $consent, [], $consent->toArray(), $request);

        return redirect()->route('settings.data-privacy')->with('success', 'Consent data berhasil dicatat.');
    }

    public function storeRetention(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'platform_data_privacy_policy_id' => ['nullable', 'exists:platform_data_privacy_policies,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'job_number' => ['required', 'string', 'max:100', 'unique:platform_data_retention_jobs,job_number'],
            'data_domain' => ['required', Rule::in(array_keys($this->fieldCatalog()))],
            'scope_reference' => ['nullable', 'string', 'max:160'],
            'cutoff_date' => ['required', 'date'],
            'action_type' => ['required', Rule::in(['archive', 'anonymize', 'delete', 'legal_hold_review'])],
            'estimated_records' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'approved', 'blocked', 'completed'])],
            'scheduled_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $job = PlatformDataRetentionJob::create([
            ...$data,
            'job_number' => strtoupper($data['job_number']),
            'legal_hold' => $request->boolean('legal_hold'),
            'impact_snapshot' => [
                'action' => $data['action_type'],
                'estimated_records' => (int) $data['estimated_records'],
                'requires_approval' => in_array($data['action_type'], ['delete', 'anonymize'], true),
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('platform_data_retention_job.created', "Retention job {$job->job_number} dibuat.", $job, [], $job->toArray(), $request);

        return redirect()->route('settings.data-privacy')->with('success', 'Retention job berhasil disimpan.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(Gate::allows('manage modules') || Gate::allows('manage users') || Gate::allows('view delivery progress'), 403);
    }

    private function fieldCatalog(): array
    {
        return [
            'employee_master' => ['NIK', 'NPWP', 'alamat', 'nomor HP', 'email pribadi', 'kontak darurat'],
            'payroll' => ['gaji pokok', 'rekening bank', 'slip gaji', 'PPh 21', 'BPJS'],
            'attendance' => ['lokasi clock-in', 'device id', 'foto liveness', 'jadwal shift'],
            'recruitment' => ['CV', 'hasil interview', 'hasil psikotest', 'expected salary'],
            'documents' => ['KTP', 'KK', 'kontrak kerja', 'dokumen medis', 'surat peringatan'],
            'ess' => ['request cuti', 'reimbursement', 'profile update', 'survey'],
        ];
    }
}
