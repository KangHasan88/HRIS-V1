<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicApiWebhookController extends Controller
{
    public function index(): View
    {
        $this->authorizeIntegration();

        $endpoints = $this->endpoints();
        $webhooks = $this->webhookEvents();
        $logs = $this->integrationLogs();
        $rateLimits = $this->rateLimitPolicies();
        $readiness = $this->readiness($endpoints, $webhooks, $logs);

        return view('integrations.public-api-webhooks', [
            'active' => 'public-api-webhooks',
            'summary' => [
                'readiness' => $readiness['score'],
                'endpoints' => $endpoints->count(),
                'webhooks' => $webhooks->count(),
                'enabled_endpoints' => $endpoints->where('status', 'ready')->count(),
                'recent_logs' => $logs->count(),
                'failed_jobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->where('failed_at', '>=', now()->subDays(7))->count() : 0,
            ],
            'endpoints' => $endpoints,
            'webhooks' => $webhooks,
            'logs' => $logs,
            'rateLimits' => $rateLimits,
            'readiness' => $readiness,
            'actions' => $this->actions($readiness, $logs),
        ]);
    }

    private function endpoints(): Collection
    {
        return collect([
            ['code' => 'employees.read', 'method' => 'GET', 'path' => '/api/v1/employees', 'module' => 'Core HR', 'scope' => 'hris.employee.read', 'status' => 'ready', 'note' => 'Data employee, organisasi, status kerja, dan kontak kerja.'],
            ['code' => 'attendance.events.read', 'method' => 'GET', 'path' => '/api/v1/attendance/events', 'module' => 'Attendance', 'scope' => 'hris.attendance.read', 'status' => 'ready', 'note' => 'Clock event valid untuk BI, payroll, dan integrasi attendance.'],
            ['code' => 'leave.requests.read', 'method' => 'GET', 'path' => '/api/v1/leave/requests', 'module' => 'Leave', 'scope' => 'hris.leave.read', 'status' => 'ready', 'note' => 'Pengajuan cuti, approval, saldo, dan attachment reference.'],
            ['code' => 'payroll.runs.read', 'method' => 'GET', 'path' => '/api/v1/payroll/runs', 'module' => 'Payroll', 'scope' => 'hris.payroll.read', 'status' => 'guarded', 'note' => 'Butuh approval ekstra karena mengandung kompensasi dan pajak.'],
            ['code' => 'webhook.delivery.retry', 'method' => 'POST', 'path' => '/api/v1/webhooks/retry', 'module' => 'Integration', 'scope' => 'hris.integration.write', 'status' => 'guarded', 'note' => 'Retry webhook harus idempotent dan tercatat audit log.'],
            ['code' => 'public.psychotest.result', 'method' => 'POST', 'path' => '/api/v1/recruitment/psychotest/results', 'module' => 'Recruitment', 'scope' => 'hris.recruitment.write', 'status' => 'ready', 'note' => 'Endpoint penerimaan hasil psikotest eksternal/internal.'],
        ]);
    }

    private function webhookEvents(): Collection
    {
        return collect([
            ['event' => 'employee.created', 'module' => 'Core HR', 'delivery' => 'near real-time', 'payload' => 'employee_id, employee_number, company, branch, status', 'status' => 'ready'],
            ['event' => 'attendance.clock_synced', 'module' => 'Attendance', 'delivery' => 'batch/agent', 'payload' => 'batch_id, connector_id, pulled_rows, failed_rows', 'status' => 'ready'],
            ['event' => 'leave.approved', 'module' => 'Leave', 'delivery' => 'near real-time', 'payload' => 'request_id, employee_id, days, approver, decided_at', 'status' => 'ready'],
            ['event' => 'payroll.closed', 'module' => 'Payroll', 'delivery' => 'after closing', 'payload' => 'run_id, period, employee_count, net_estimate, anomaly_count', 'status' => 'guarded'],
            ['event' => 'coretax.submission_updated', 'module' => 'Tax', 'delivery' => 'polling', 'payload' => 'submission_number, status, accepted_rows, rejected_rows', 'status' => 'ready'],
            ['event' => 'ess.request.submitted', 'module' => 'ESS', 'delivery' => 'near real-time', 'payload' => 'request_type, employee_id, submitted_at, current_approver', 'status' => 'ready'],
        ]);
    }

    private function rateLimitPolicies(): Collection
    {
        return collect([
            ['tier' => 'Internal trusted service', 'limit' => '600 request / minute', 'burst' => '1200 / 5 minute', 'auth' => 'API key + IP allowlist', 'use_case' => 'BI, payroll bank export, internal integration.'],
            ['tier' => 'Partner integration', 'limit' => '120 request / minute', 'burst' => '300 / 5 minute', 'auth' => 'API key + HMAC signature', 'use_case' => 'Vendor psikotest, attendance, outsourcing.'],
            ['tier' => 'Public callback', 'limit' => '60 request / minute', 'burst' => '120 / 5 minute', 'auth' => 'Webhook secret + timestamp', 'use_case' => 'Callback result, webhook delivery status.'],
        ]);
    }

    private function integrationLogs(): Collection
    {
        return AuditLog::query()
            ->where(function ($query) {
                $query->where('event', 'like', '%integration%')
                    ->orWhere('event', 'like', '%webhook%')
                    ->orWhere('event', 'like', '%api%')
                    ->orWhere('event', 'like', '%coretax%')
                    ->orWhere('event', 'like', '%biometric%');
            })
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (AuditLog $log) => [
                'event' => $log->event,
                'summary' => $log->summary,
                'actor' => $log->actor?->name ?? 'System',
                'ip' => $log->ip_address ?? '-',
                'created_at' => $log->created_at,
            ]);
    }

    private function readiness(Collection $endpoints, Collection $webhooks, Collection $logs): array
    {
        $guarded = $endpoints->where('status', 'guarded')->count() + $webhooks->where('status', 'guarded')->count();
        $score = 100 - min(30, $guarded * 5);
        $score -= $logs->isEmpty() ? 10 : 0;

        return [
            'score' => max(0, $score),
            'guarded_count' => $guarded,
            'status' => $score >= 85 ? 'ready' : 'review',
            'message' => $score >= 85
                ? 'API dan webhook catalog siap untuk tahap hardening credential dan worker.'
                : 'Masih ada endpoint sensitif yang perlu policy tambahan sebelum production.',
        ];
    }

    private function actions(array $readiness, Collection $logs): Collection
    {
        $actions = collect([
            'Tetapkan owner dan expiry date untuk setiap API key.',
            'Gunakan HMAC signature untuk webhook yang menerima data eksternal.',
            'Aktifkan idempotency key untuk endpoint POST agar retry aman.',
        ]);

        if ($readiness['guarded_count'] > 0) {
            $actions->push('Endpoint guarded perlu approval security dan audit masking sebelum production.');
        }

        if ($logs->isEmpty()) {
            $actions->push('Tambahkan audit log untuk setiap request API, webhook delivery, dan retry.');
        }

        return $actions->unique()->values();
    }

    private function authorizeIntegration(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage modules') || $user?->hasPermission('view delivery progress'), 403);
    }
}
