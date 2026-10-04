<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CoretaxConnectorSetting;
use App\Models\CoretaxRetryLog;
use App\Models\CoretaxSubmissionTracker;
use App\Models\TaxMonthlyExportTemplate;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CoretaxConnectorController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.coretax-connector', [
            'active' => 'coretax-connector',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'settings' => CoretaxConnectorSetting::with('company')->latest()->limit(50)->get(),
            'templates' => TaxMonthlyExportTemplate::with('package.company')->latest()->limit(50)->get(),
            'retryLogs' => CoretaxRetryLog::with(['setting.company', 'template.package', 'submissions'])->latest()->limit(100)->get(),
            'submissions' => CoretaxSubmissionTracker::with(['template.package.company', 'retryLog'])->latest()->limit(100)->get(),
            'summary' => [
                'settings' => CoretaxConnectorSetting::count(),
                'active' => CoretaxConnectorSetting::where('is_active', true)->count(),
                'queued' => CoretaxRetryLog::where('status', 'queued')->count(),
                'failed' => CoretaxRetryLog::where('status', 'failed')->count(),
                'submitted' => CoretaxSubmissionTracker::count(),
                'accepted' => CoretaxSubmissionTracker::where('submission_status', 'accepted')->count(),
                'rejected' => CoretaxSubmissionTracker::where('submission_status', 'rejected')->count(),
                'difference' => CoretaxSubmissionTracker::sum('difference_amount'),
            ],
        ]);
    }

    public function storeSetting(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'environment' => ['required', 'in:sandbox,production'],
            'base_url' => ['required', 'url', 'max:255'],
            'client_id' => ['required', 'string', 'max:100'],
            'client_secret' => ['required', 'string', 'min:8', 'max:255'],
            'timeout_seconds' => ['required', 'integer', 'min:5', 'max:120'],
            'max_retry' => ['required', 'integer', 'min:0', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $setting = CoretaxConnectorSetting::create([
            'company_id' => $data['company_id'] ?? null,
            'environment' => $data['environment'],
            'base_url' => rtrim($data['base_url'], '/'),
            'client_id' => $data['client_id'],
            'secret_mask' => $this->maskSecret($data['client_secret']),
            'credential_hash' => hash('sha256', $data['client_id'] . '|' . $data['client_secret']),
            'timeout_seconds' => $data['timeout_seconds'],
            'max_retry' => $data['max_retry'],
            'is_active' => $request->boolean('is_active', true),
            'last_checked_at' => now(),
            'last_status' => 'configured',
        ]);

        AuditLogger::record(
            'coretax_connector.setting_created',
            "Setting connector Coretax {$setting->environment} dibuat.",
            $setting,
            [],
            $setting->only(['company_id', 'environment', 'base_url', 'client_id', 'secret_mask', 'timeout_seconds', 'max_retry']),
            $request
        );

        return redirect()->route('payroll.coretax-connector')->with('success', 'Setting connector Coretax berhasil disimpan.');
    }

    public function queueTemplate(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'coretax_connector_setting_id' => ['required', 'exists:coretax_connector_settings,id'],
            'tax_monthly_export_template_id' => ['required', 'exists:tax_monthly_export_templates,id'],
            'request_type' => ['required', 'in:validate_template,submit_template,status_check'],
        ]);

        $setting = CoretaxConnectorSetting::findOrFail($data['coretax_connector_setting_id']);
        $template = TaxMonthlyExportTemplate::findOrFail($data['tax_monthly_export_template_id']);

        $log = CoretaxRetryLog::create([
            'coretax_connector_setting_id' => $setting->id,
            'tax_monthly_export_template_id' => $template->id,
            'request_type' => $data['request_type'],
            'status' => 'queued',
            'attempt' => 0,
            'max_retry' => $setting->max_retry,
            'next_retry_at' => now()->addMinutes(5),
            'endpoint' => $setting->base_url . '/api/coretax/' . $data['request_type'],
            'payload_checksum' => $template->checksum,
            'request_snapshot' => [
                'template_number' => $template->template_number,
                'schema_version' => $template->schema_version,
                'format' => $template->format,
                'payload_rows' => count($template->payload_snapshot ?? []),
            ],
            'response_snapshot' => [],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'coretax_connector.retry_queued',
            "Request Coretax {$log->request_type} masuk retry queue.",
            $log,
            [],
            $log->only(['request_type', 'status', 'attempt', 'max_retry', 'endpoint', 'payload_checksum']),
            $request
        );

        return redirect()->route('payroll.coretax-connector')->with('success', 'Template Coretax masuk retry queue.');
    }

    public function trackSubmission(Request $request, CoretaxRetryLog $retryLog): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'submission_status' => ['required', 'in:processing,accepted,rejected,partial'],
            'accepted_rows' => ['required', 'integer', 'min:0'],
            'rejected_rows' => ['required', 'integer', 'min:0'],
            'acknowledged_total_pph21' => ['required', 'numeric', 'min:0'],
            'response_message' => ['nullable', 'string', 'max:500'],
        ]);

        $template = $retryLog->template()->firstOrFail();
        $payload = collect($template->payload_snapshot ?? []);
        $submittedRows = $payload->count();
        $templateTotal = (float) $payload->sum('pph21');
        $acknowledgedTotal = (float) $data['acknowledged_total_pph21'];
        $difference = round($acknowledgedTotal - $templateTotal, 2);

        $tracker = CoretaxSubmissionTracker::create([
            'coretax_retry_log_id' => $retryLog->id,
            'tax_monthly_export_template_id' => $template->id,
            'submission_number' => $this->submissionNumber(),
            'submission_status' => $data['submission_status'],
            'submitted_rows' => $submittedRows,
            'accepted_rows' => $data['accepted_rows'],
            'rejected_rows' => $data['rejected_rows'],
            'template_total_pph21' => $templateTotal,
            'acknowledged_total_pph21' => $acknowledgedTotal,
            'difference_amount' => $difference,
            'reconciliation_snapshot' => [
                'template_number' => $template->template_number,
                'template_rows' => $submittedRows,
                'accepted_rows' => $data['accepted_rows'],
                'rejected_rows' => $data['rejected_rows'],
                'template_total_pph21' => $templateTotal,
                'acknowledged_total_pph21' => $acknowledgedTotal,
                'difference_amount' => $difference,
                'response_message' => $data['response_message'] ?? null,
            ],
            'status_history' => [
                ['status' => $data['submission_status'], 'at' => now()->toDateTimeString(), 'message' => $data['response_message'] ?? null],
            ],
            'submitted_at' => now(),
            'last_checked_at' => now(),
            'created_by' => auth()->id(),
        ]);

        $retryLog->update([
            'status' => $data['submission_status'] === 'accepted' ? 'success' : ($data['submission_status'] === 'rejected' ? 'failed' : 'processing'),
            'attempt' => min($retryLog->max_retry, $retryLog->attempt + 1),
            'response_code' => $data['submission_status'] === 'accepted' ? 200 : 202,
            'response_message' => $data['response_message'] ?? 'Coretax status tracker updated.',
            'response_snapshot' => $tracker->reconciliation_snapshot,
        ]);

        AuditLogger::record(
            'coretax_submission.tracked',
            "Status submit Coretax {$tracker->submission_number} diperbarui.",
            $tracker,
            [],
            $tracker->only(['submission_number', 'submission_status', 'submitted_rows', 'accepted_rows', 'rejected_rows', 'difference_amount']),
            $request
        );

        return redirect()->route('payroll.coretax-connector')->with('success', 'Status submit Coretax berhasil dicatat.');
    }

    private function maskSecret(string $secret): string
    {
        return str_repeat('*', max(4, strlen($secret) - 4)) . substr($secret, -4);
    }

    private function submissionNumber(): string
    {
        return 'CT-SUB-' . now()->format('YmdHis') . '-' . random_int(100, 999);
    }
}
