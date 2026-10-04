<?php

namespace App\Http\Controllers;

use App\Models\BiometricConnector;
use App\Models\BiometricSyncLog;
use App\Models\CoretaxConnectorSetting;
use App\Models\CoretaxSubmissionTracker;
use App\Models\EmailCalendarConnector;
use App\Models\LeaveRequest;
use App\Models\NotificationDelivery;
use App\Models\OvertimeRequest;
use App\Models\WhatsappGatewaySetting;
use App\Models\WhatsappMessageQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class IntegrationHealthController extends Controller
{
    public function index(): View
    {
        $this->authorizeMonitoring();

        $queue = $this->queueHealth();
        $sla = $this->slaHealth();
        $connectors = $this->connectorHealth();
        $notifications = $this->notificationHealth();
        $overallScore = $this->overallScore($queue, $sla, $connectors, $notifications);

        return view('settings.integration-health', [
            'active' => 'integration-health',
            'summary' => [
                'overall_score' => $overallScore,
                'queue_backlog' => $queue['pending_jobs'],
                'failed_jobs' => $queue['failed_jobs'],
                'sla_breach' => $sla['breach_count'],
                'connector_issue' => $connectors->where('status', '!=', 'healthy')->count(),
                'notification_issue' => $notifications['issue_count'],
            ],
            'queue' => $queue,
            'sla' => $sla,
            'connectors' => $connectors,
            'notifications' => $notifications,
            'actions' => $this->actions($queue, $sla, $connectors, $notifications),
        ]);
    }

    private function queueHealth(): array
    {
        $pendingJobs = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
        $oldestJob = Schema::hasTable('jobs') ? DB::table('jobs')->orderBy('created_at')->first() : null;
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->where('failed_at', '>=', now()->subDays(7))->count() : 0;
        $failedByQueue = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->select('queue', DB::raw('count(*) as total'))->where('failed_at', '>=', now()->subDays(7))->groupBy('queue')->orderByDesc('total')->limit(5)->get()
            : collect();
        $oldestAgeMinutes = $oldestJob ? max(0, now()->diffInMinutes(\Carbon\Carbon::createFromTimestamp((int) $oldestJob->created_at))) : 0;

        return [
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
            'oldest_age_minutes' => $oldestAgeMinutes,
            'status' => $failedJobs > 0 || $oldestAgeMinutes > 30 ? 'warning' : 'healthy',
            'failed_by_queue' => $failedByQueue,
        ];
    }

    private function slaHealth(): array
    {
        $pendingLeave = LeaveRequest::where('approval_status', 'pending')->where('submitted_at', '<=', now()->subHours(24))->count();
        $pendingOvertime = OvertimeRequest::where('approval_status', 'pending')->where('submitted_at', '<=', now()->subHours(12))->count();
        $recentLeave = LeaveRequest::where('created_at', '>=', now()->subDays(7))->count();
        $recentOvertime = OvertimeRequest::where('created_at', '>=', now()->subDays(7))->count();
        $breachCount = $pendingLeave + $pendingOvertime;

        return [
            'pending_leave_over_24h' => $pendingLeave,
            'pending_overtime_over_12h' => $pendingOvertime,
            'recent_leave' => $recentLeave,
            'recent_overtime' => $recentOvertime,
            'breach_count' => $breachCount,
            'status' => $breachCount > 0 ? 'warning' : 'healthy',
        ];
    }

    private function connectorHealth(): \Illuminate\Support\Collection
    {
        return collect([
            [
                'name' => 'Biometric / Attendance Agent',
                'total' => BiometricConnector::count(),
                'active' => BiometricConnector::whereIn('status', ['active', 'connected'])->count(),
                'last_seen' => BiometricConnector::max('last_sync_at'),
                'issue' => BiometricSyncLog::whereIn('status', ['failed', 'error'])->where('started_at', '>=', now()->subDays(7))->count(),
                'route' => 'integrations.biometric-connectors',
            ],
            [
                'name' => 'Coretax Connector',
                'total' => CoretaxConnectorSetting::count(),
                'active' => CoretaxConnectorSetting::where('is_active', true)->count(),
                'last_seen' => CoretaxConnectorSetting::max('last_checked_at') ?: CoretaxSubmissionTracker::max('submitted_at'),
                'issue' => CoretaxSubmissionTracker::whereIn('submission_status', ['failed', 'rejected', 'error'])->where('created_at', '>=', now()->subDays(7))->count(),
                'route' => 'payroll.coretax-connector',
            ],
            [
                'name' => 'Email & Calendar',
                'total' => EmailCalendarConnector::count(),
                'active' => EmailCalendarConnector::whereIn('status', ['active', 'connected'])->count(),
                'last_seen' => EmailCalendarConnector::max('last_sync_at'),
                'issue' => EmailCalendarConnector::whereIn('status', ['failed', 'error'])->count(),
                'route' => 'integrations.email-calendar',
            ],
            [
                'name' => 'WhatsApp Gateway',
                'total' => WhatsappGatewaySetting::count(),
                'active' => WhatsappGatewaySetting::whereIn('status', ['active', 'connected'])->count(),
                'last_seen' => WhatsappGatewaySetting::max('last_ping_at'),
                'issue' => WhatsappMessageQueue::whereIn('status', ['failed', 'error'])->where('created_at', '>=', now()->subDays(7))->count(),
                'route' => 'integrations.whatsapp-gateway',
            ],
        ])->map(function (array $item) {
            $status = $item['issue'] > 0 ? 'warning' : ($item['total'] > 0 && $item['active'] === 0 ? 'inactive' : 'healthy');
            $item['status'] = $status;
            $item['label'] = [
                'healthy' => 'Sehat',
                'warning' => 'Perlu Review',
                'inactive' => 'Belum Aktif',
            ][$status];

            return $item;
        });
    }

    private function notificationHealth(): array
    {
        $queued = NotificationDelivery::where('status', 'queued')->count();
        $failed = NotificationDelivery::whereIn('status', ['failed', 'error'])->where('created_at', '>=', now()->subDays(7))->count();
        $stale = NotificationDelivery::where('status', 'queued')->where('created_at', '<=', now()->subHours(2))->count();
        $sent = NotificationDelivery::where('status', 'sent')->where('created_at', '>=', now()->subDays(7))->count();

        return [
            'queued' => $queued,
            'failed' => $failed,
            'stale' => $stale,
            'sent_7d' => $sent,
            'issue_count' => $failed + $stale,
            'status' => ($failed + $stale) > 0 ? 'warning' : 'healthy',
        ];
    }

    private function overallScore(array $queue, array $sla, \Illuminate\Support\Collection $connectors, array $notifications): int
    {
        $score = 100;
        $score -= min(30, $queue['failed_jobs'] * 8);
        $score -= min(20, $queue['oldest_age_minutes'] > 30 ? 15 : 0);
        $score -= min(25, $sla['breach_count'] * 6);
        $score -= min(20, $connectors->where('status', 'warning')->count() * 8);
        $score -= min(15, $notifications['issue_count'] * 5);

        return max(0, $score);
    }

    private function actions(array $queue, array $sla, \Illuminate\Support\Collection $connectors, array $notifications): \Illuminate\Support\Collection
    {
        $actions = collect();

        if ($queue['failed_jobs'] > 0) {
            $actions->push('Review failed_jobs dan retry job yang aman setelah root cause jelas.');
        }
        if ($queue['oldest_age_minutes'] > 30) {
            $actions->push('Cek queue worker, supervisor, atau cron karena backlog job sudah menua.');
        }
        if ($sla['breach_count'] > 0) {
            $actions->push('Follow-up approval cuti/lembur yang melewati SLA agar ESS tidak macet.');
        }
        if ($connectors->where('status', 'warning')->isNotEmpty()) {
            $actions->push('Prioritaskan connector dengan status Perlu Review dan cek log integrasi 7 hari terakhir.');
        }
        if ($notifications['issue_count'] > 0) {
            $actions->push('Cek notifikasi queued/stale agar reminder approval dan ESS tetap terkirim.');
        }

        return $actions->whenEmpty(fn ($items) => $items->push('Health monitoring normal. Lanjutkan review rutin harian sebelum jam operasional.'))->values();
    }

    private function authorizeMonitoring(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage modules') || $user?->hasPermission('view delivery progress'), 403);
    }
}
