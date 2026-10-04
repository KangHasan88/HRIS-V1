<?php

namespace App\Http\Controllers;

use App\Models\AttendanceClockEvent;
use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeDisciplinaryRecord;
use App\Models\EmployeeProfile;
use App\Models\EngagementSurveyResponse;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PerformanceGoalCheckIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AttritionRiskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAnalytics();

        $periodStart = Carbon::parse($request->input('start_date', now()->subDays(90)->toDateString()))->startOfDay();
        $periodEnd = Carbon::parse($request->input('end_date', now()->toDateString()))->endOfDay();
        $companyId = $request->integer('company_id') ?: null;
        $branchId = $request->integer('branch_id') ?: null;
        $riskFilter = $request->input('risk_level');

        $employees = EmployeeProfile::with(['company', 'branch'])
            ->where('is_active', true)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('employee_number')
            ->limit(250)
            ->get();

        $profiles = $employees->map(fn (EmployeeProfile $employee) => $this->riskProfile($employee, $periodStart, $periodEnd))
            ->when($riskFilter, fn (Collection $items) => $items->where('risk_level', $riskFilter))
            ->sortByDesc('score')
            ->values();

        return view('analytics.attrition-risk', [
            'active' => 'attrition-risk',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'filters' => [
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'risk_level' => $riskFilter,
            ],
            'profiles' => $profiles,
            'summary' => [
                'employees' => $profiles->count(),
                'high' => $profiles->where('risk_level', 'high')->count(),
                'medium' => $profiles->where('risk_level', 'medium')->count(),
                'low' => $profiles->where('risk_level', 'low')->count(),
                'avg_score' => (int) round($profiles->avg('score') ?? 0),
            ],
            'topFactors' => $this->topFactors($profiles),
            'recommendationBuckets' => $this->recommendationBuckets($profiles),
        ]);
    }

    private function riskProfile(EmployeeProfile $employee, Carbon $periodStart, Carbon $periodEnd): array
    {
        $clockIns = AttendanceClockEvent::where('employee_profile_id', $employee->id)
            ->where('event_type', 'clock_in')
            ->whereBetween('event_at', [$periodStart, $periodEnd])
            ->get();
        $lateCount = $clockIns->filter(fn (AttendanceClockEvent $event) => $event->event_at?->format('H:i') > '08:30')->count();
        $lateRate = $clockIns->count() > 0 ? ($lateCount / $clockIns->count()) * 100 : 0;

        $leaveDays = (float) LeaveRequest::where('employee_profile_id', $employee->id)
            ->whereBetween('start_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum('requested_days');
        $overtimeHours = (float) OvertimeRequest::where('employee_profile_id', $employee->id)
            ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum('requested_hours');
        $engagement = EngagementSurveyResponse::where('employee_profile_id', $employee->id)
            ->latest('submitted_at')
            ->first();
        $goalRisk = PerformanceGoalCheckIn::where('employee_profile_id', $employee->id)
            ->whereBetween('checkin_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->latest('checkin_date')
            ->first();
        $disciplinary = EmployeeDisciplinaryRecord::where('employee_profile_id', $employee->id)
            ->whereBetween('incident_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereIn('status', ['open', 'active', 'follow_up'])
            ->count();

        $contractRisk = $employee->contract_end_date && $employee->contract_end_date->between(now(), now()->addDays(60));
        $tenureMonths = $employee->join_date ? $employee->join_date->diffInMonths(now()) : null;

        $factors = collect([
            $this->factor('Absensi terlambat', $lateRate >= 15 ? 18 : ($lateRate >= 8 ? 10 : 0), $lateCount.' terlambat dari '.$clockIns->count().' clock-in', 'Review pola shift, commute, dan coaching atasan.'),
            $this->factor('Engagement rendah', $engagement && $engagement->engagement_score < 60 ? 22 : ($engagement && $engagement->engagement_score < 75 ? 10 : 0), $engagement ? 'Engagement score '.$engagement->engagement_score : 'Belum ada response engagement personal', 'Lakukan check-in 1:1 dan gali blocker kerja.'),
            $this->factor('eNPS rendah', $engagement && $engagement->enps_score <= 6 ? 14 : 0, $engagement ? 'eNPS '.$engagement->enps_score : 'Belum ada eNPS personal', 'Tanyakan faktor rekomendasi perusahaan dan area perbaikan.'),
            $this->factor('Performa berisiko', $goalRisk && in_array($goalRisk->risk_status, ['at_risk', 'off_track'], true) ? 18 : 0, $goalRisk ? 'Goal '.$goalRisk->risk_status.' progress '.number_format((float) $goalRisk->progress_percent, 0).'%' : 'Belum ada check-in goal', 'Susun recovery plan, unblock pekerjaan, dan pastikan ekspektasi jelas.'),
            $this->factor('Disiplin/coaching aktif', $disciplinary > 0 ? min(16, $disciplinary * 8) : 0, $disciplinary.' case aktif periode ini', 'Pastikan follow-up coaching terdokumentasi dan fair.'),
            $this->factor('Overtime tinggi', $overtimeHours >= 40 ? 12 : ($overtimeHours >= 20 ? 6 : 0), number_format($overtimeHours, 1).' jam lembur', 'Review workload, manpower gap, dan fatigue risk.'),
            $this->factor('Cuti tinggi', $leaveDays >= 8 ? 8 : 0, number_format($leaveDays, 1).' hari cuti', 'Cek apakah cuti terencana, sakit, burnout, atau issue personal.'),
            $this->factor('Kontrak segera berakhir', $contractRisk ? 12 : 0, $employee->contract_end_date ? 'Kontrak sampai '.$employee->contract_end_date->format('d M Y') : 'Tidak ada kontrak berakhir dekat', 'Mulai keputusan renewal atau retention plan sebelum terlambat.'),
            $this->factor('Tenure awal', $tenureMonths !== null && $tenureMonths < 6 ? 6 : 0, $tenureMonths !== null ? $tenureMonths.' bulan masa kerja' : 'Join date belum lengkap', 'Pastikan onboarding, buddy, dan probation review berjalan.'),
        ]);

        $score = min(100, (int) $factors->sum('score'));
        $riskLevel = $score >= 60 ? 'high' : ($score >= 30 ? 'medium' : 'low');
        $dominantFactors = $factors->where('score', '>', 0)->sortByDesc('score')->values();

        return [
            'employee' => $employee,
            'score' => $score,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'dominant_factors' => $dominantFactors,
            'primary_factor' => $dominantFactors->first()['label'] ?? 'Tidak ada faktor dominan',
            'recommendation' => $this->recommendation($riskLevel, $dominantFactors),
        ];
    }

    private function factor(string $label, int $score, string $evidence, string $action): array
    {
        return compact('label', 'score', 'evidence', 'action');
    }

    private function recommendation(string $riskLevel, Collection $factors): string
    {
        if ($riskLevel === 'high') {
            return 'Prioritaskan 1:1 dengan atasan dan HRBP minggu ini, cek kompensasi/workload, lalu buat action plan retention.';
        }

        if ($riskLevel === 'medium') {
            return 'Jadwalkan check-in ringan, review blocker, dan pantau ulang dalam 30 hari.';
        }

        return $factors->isEmpty()
            ? 'Risiko rendah. Tetap jaga komunikasi dan engagement rutin.'
            : 'Risiko rendah dengan sinyal minor. Pantau lewat cycle people analytics berikutnya.';
    }

    private function topFactors(Collection $profiles): Collection
    {
        return $profiles
            ->flatMap(fn (array $profile) => $profile['dominant_factors'])
            ->groupBy('label')
            ->map(fn (Collection $items, string $label) => [
                'label' => $label,
                'count' => $items->count(),
                'score' => $items->sum('score'),
            ])
            ->sortByDesc('score')
            ->take(6)
            ->values();
    }

    private function recommendationBuckets(Collection $profiles): array
    {
        return [
            'one_on_one' => $profiles->where('risk_level', 'high')->count(),
            'monitor_30_days' => $profiles->where('risk_level', 'medium')->count(),
            'routine_engagement' => $profiles->where('risk_level', 'low')->count(),
        ];
    }

    private function authorizeAnalytics(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance') || $user?->hasPermission('view delivery progress') || $user?->hasPermission('manage modules'), 403);
    }
}
