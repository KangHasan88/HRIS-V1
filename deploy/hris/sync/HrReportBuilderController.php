<?php

namespace App\Http\Controllers;

use App\Models\AttendanceClockEvent;
use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\EngagementSurveyResponse;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollRun;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HrReportBuilderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAnalytics();

        $prompt = trim((string) $request->input('prompt', 'Buat ringkasan HR bulan ini untuk leader: headcount, absensi, cuti, lembur, payroll, engagement, risiko, dan action item.'));
        $periodStart = Carbon::parse($request->input('start_date', now()->startOfMonth()->toDateString()))->startOfDay();
        $periodEnd = Carbon::parse($request->input('end_date', now()->toDateString()))->endOfDay();
        $companyId = $request->integer('company_id') ?: null;
        $branchId = $request->integer('branch_id') ?: null;
        $audience = $request->input('audience', 'leader');

        $metrics = $this->metrics($periodStart, $periodEnd, $companyId, $branchId);
        $intent = $this->detectIntent($prompt);
        $report = $this->buildReport($prompt, $intent, $metrics, $periodStart, $periodEnd, $audience);

        return view('analytics.hr-report-builder', [
            'active' => 'hr-report-builder',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'filters' => [
                'prompt' => $prompt,
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'audience' => $audience,
            ],
            'intent' => $intent,
            'metrics' => $metrics,
            'report' => $report,
            'promptExamples' => [
                'Buat ringkasan absensi dan keterlambatan untuk weekly meeting.',
                'Buat laporan payroll dan lembur yang perlu direview CFO.',
                'Buat executive summary people analytics untuk direksi.',
                'Apa risiko HR terbesar bulan ini dan action item-nya?',
                'Buat update engagement dan attrition risk untuk HRBP.',
            ],
        ]);
    }

    private function metrics(Carbon $periodStart, Carbon $periodEnd, ?int $companyId, ?int $branchId): array
    {
        $employeeQuery = EmployeeProfile::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));

        $employeeIds = (clone $employeeQuery)->pluck('id');
        $activeEmployees = (clone $employeeQuery)->where('is_active', true)->count();
        $newJoiners = (clone $employeeQuery)->whereBetween('join_date', [$periodStart->toDateString(), $periodEnd->toDateString()])->count();
        $contractExpiring = (clone $employeeQuery)->whereNotNull('contract_end_date')->whereBetween('contract_end_date', [now()->toDateString(), now()->addDays(60)->toDateString()])->count();

        $clockEvents = AttendanceClockEvent::whereIn('employee_profile_id', $employeeIds)
            ->whereBetween('event_at', [$periodStart, $periodEnd])
            ->get();
        $clockIns = $clockEvents->where('event_type', 'clock_in');
        $lateClockIns = $clockIns->filter(fn (AttendanceClockEvent $event) => $event->event_at?->format('H:i') > '08:30')->count();

        $leaveRequests = LeaveRequest::whereIn('employee_profile_id', $employeeIds)
            ->whereBetween('start_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get();
        $overtimeRequests = OvertimeRequest::whereIn('employee_profile_id', $employeeIds)
            ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get();
        $engagementResponses = EngagementSurveyResponse::whereIn('employee_profile_id', $employeeIds)
            ->whereBetween('submitted_at', [$periodStart, $periodEnd])
            ->get();
        $payrollRuns = PayrollRun::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->whereBetween('cutoff_start', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get();

        return [
            'headcount' => $activeEmployees,
            'new_joiners' => $newJoiners,
            'contract_expiring' => $contractExpiring,
            'clock_events' => $clockEvents->count(),
            'clock_ins' => $clockIns->count(),
            'late_clock_ins' => $lateClockIns,
            'late_rate' => $clockIns->count() > 0 ? round(($lateClockIns / $clockIns->count()) * 100, 1) : 0,
            'leave_requests' => $leaveRequests->count(),
            'leave_days' => round((float) $leaveRequests->sum('requested_days'), 1),
            'pending_leave' => $leaveRequests->where('approval_status', 'pending')->count(),
            'overtime_requests' => $overtimeRequests->count(),
            'overtime_hours' => round((float) $overtimeRequests->sum('requested_hours'), 1),
            'approved_overtime_hours' => round((float) $overtimeRequests->sum('approved_hours'), 1),
            'engagement_responses' => $engagementResponses->count(),
            'avg_engagement' => round((float) ($engagementResponses->avg('engagement_score') ?? 0), 1),
            'avg_enps' => round((float) ($engagementResponses->avg('enps_score') ?? 0), 1),
            'payroll_runs' => $payrollRuns->count(),
            'payroll_blocked' => $payrollRuns->where('status', 'blocked')->count(),
            'payroll_anomalies' => $payrollRuns->sum('anomaly_count'),
            'net_pay_estimate' => (float) $payrollRuns->sum('net_estimate'),
        ];
    }

    private function detectIntent(string $prompt): array
    {
        $text = str($prompt)->lower()->toString();
        $map = [
            'attendance' => ['absensi', 'terlambat', 'clock', 'shift', 'attendance'],
            'payroll' => ['payroll', 'gaji', 'lembur', 'overtime', 'pph', 'bpjs'],
            'engagement' => ['engagement', 'survey', 'enps', 'attrition', 'retention'],
            'executive' => ['direksi', 'leader', 'executive', 'ringkasan', 'summary'],
            'risk' => ['risiko', 'risk', 'anomali', 'blocked', 'action'],
        ];

        $scores = collect($map)->map(fn (array $keywords) => collect($keywords)->filter(fn (string $keyword) => str_contains($text, $keyword))->count());
        $focus = $scores->sortDesc()->keys()->first() ?: 'executive';

        return [
            'focus' => $focus,
            'label' => [
                'attendance' => 'Absensi & Kedisiplinan',
                'payroll' => 'Payroll & Cost',
                'engagement' => 'Engagement & Retention',
                'executive' => 'Executive Summary',
                'risk' => 'Risk & Action',
            ][$focus],
            'confidence' => min(96, 58 + ((int) $scores->max() * 12)),
        ];
    }

    private function buildReport(string $prompt, array $intent, array $metrics, Carbon $periodStart, Carbon $periodEnd, string $audience): array
    {
        $period = $periodStart->format('d M Y').' - '.$periodEnd->format('d M Y');
        $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
        $riskNotes = collect();

        if ($metrics['late_rate'] >= 15) {
            $riskNotes->push('Keterlambatan tinggi: '.$metrics['late_rate'].'% dari clock-in.');
        }
        if ($metrics['pending_leave'] > 0) {
            $riskNotes->push($metrics['pending_leave'].' pengajuan cuti masih menunggu approval.');
        }
        if ($metrics['payroll_blocked'] > 0 || $metrics['payroll_anomalies'] > 0) {
            $riskNotes->push('Payroll perlu review: '.$metrics['payroll_blocked'].' run blocked dan '.$metrics['payroll_anomalies'].' anomali.');
        }
        if ($metrics['avg_engagement'] > 0 && $metrics['avg_engagement'] < 70) {
            $riskNotes->push('Engagement rendah dengan rata-rata '.$metrics['avg_engagement'].'.');
        }
        if ($metrics['contract_expiring'] > 0) {
            $riskNotes->push($metrics['contract_expiring'].' kontrak akan berakhir dalam 60 hari.');
        }

        if ($riskNotes->isEmpty()) {
            $riskNotes->push('Tidak ada sinyal risiko besar dari metrik periode ini.');
        }

        $paragraphs = [
            'Untuk periode '.$period.', laporan ini membaca '.$metrics['headcount'].' karyawan aktif, '.$metrics['new_joiners'].' karyawan baru, dan '.$metrics['contract_expiring'].' kontrak yang perlu dipantau dalam 60 hari.',
            'Dari sisi operasional, tercatat '.$metrics['clock_events'].' event absensi, '.$metrics['late_clock_ins'].' clock-in terlambat atau '.$metrics['late_rate'].'%, '.$metrics['leave_days'].' hari cuti diajukan, dan '.$metrics['overtime_hours'].' jam lembur diminta.',
            'Dari sisi payroll dan people signal, terdapat '.$metrics['payroll_runs'].' payroll run, estimasi net pay '.$money($metrics['net_pay_estimate']).', '.$metrics['payroll_anomalies'].' anomali payroll, serta engagement rata-rata '.$metrics['avg_engagement'].' dari '.$metrics['engagement_responses'].' response.',
        ];

        return [
            'title' => $intent['label'].' untuk '.ucfirst($audience),
            'prompt' => $prompt,
            'period' => $period,
            'paragraphs' => $paragraphs,
            'risks' => $riskNotes->values(),
            'actions' => $this->actions($metrics, $intent['focus']),
            'table' => $this->metricTable($metrics, $money),
        ];
    }

    private function actions(array $metrics, string $focus): Collection
    {
        $actions = collect([
            'Review approval tertunda dan pastikan SLA manager tidak macet.',
            'Gunakan ringkasan ini sebagai bahan weekly HR business review.',
        ]);

        if ($metrics['late_rate'] >= 10 || $focus === 'attendance') {
            $actions->push('Minta atasan cek pola keterlambatan dan kebutuhan shift adjustment.');
        }
        if ($metrics['payroll_anomalies'] > 0 || $focus === 'payroll') {
            $actions->push('Prioritaskan validasi payroll anomaly sebelum payroll closing.');
        }
        if ($metrics['avg_engagement'] > 0 && $metrics['avg_engagement'] < 75 || $focus === 'engagement') {
            $actions->push('Jalankan check-in engagement untuk tim dengan skor rendah.');
        }
        if ($metrics['contract_expiring'] > 0) {
            $actions->push('Buat daftar renewal kontrak dan keputusan manpower dalam 2 minggu.');
        }

        return $actions->unique()->values();
    }

    private function metricTable(array $metrics, callable $money): Collection
    {
        return collect([
            ['group' => 'Core HR', 'metric' => 'Headcount aktif', 'value' => $metrics['headcount'], 'note' => 'Karyawan aktif dalam scope filter'],
            ['group' => 'Core HR', 'metric' => 'Karyawan baru', 'value' => $metrics['new_joiners'], 'note' => 'Join date dalam periode'],
            ['group' => 'Absensi', 'metric' => 'Late rate', 'value' => $metrics['late_rate'].'%', 'note' => $metrics['late_clock_ins'].' dari '.$metrics['clock_ins'].' clock-in'],
            ['group' => 'Cuti', 'metric' => 'Hari cuti diajukan', 'value' => $metrics['leave_days'], 'note' => $metrics['pending_leave'].' pending approval'],
            ['group' => 'Lembur', 'metric' => 'Jam lembur diminta', 'value' => $metrics['overtime_hours'], 'note' => $metrics['approved_overtime_hours'].' jam approved'],
            ['group' => 'Payroll', 'metric' => 'Net pay estimate', 'value' => $money($metrics['net_pay_estimate']), 'note' => $metrics['payroll_anomalies'].' anomaly'],
            ['group' => 'Engagement', 'metric' => 'Avg engagement', 'value' => $metrics['avg_engagement'], 'note' => $metrics['engagement_responses'].' response'],
            ['group' => 'Risk', 'metric' => 'Kontrak 60 hari', 'value' => $metrics['contract_expiring'], 'note' => 'Perlu keputusan renewal/offboarding'],
        ]);
    }

    private function authorizeAnalytics(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance') || $user?->hasPermission('view delivery progress') || $user?->hasPermission('manage modules'), 403);
    }
}
