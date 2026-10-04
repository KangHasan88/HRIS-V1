<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\LeaveOvertimeCapacityPrediction;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Support\AuditLogger;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveOvertimeCapacityController extends Controller
{
    public function index(): View
    {
        $this->authorizeAnalytics();

        $predictions = LeaveOvertimeCapacityPrediction::with(['company', 'branch'])->latest()->limit(120)->get();

        return view('analytics.leave-overtime-capacity', [
            'active' => 'leave-overtime-capacity',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'predictions' => $predictions,
            'summary' => [
                'active_employee' => EmployeeProfile::where('is_active', true)->count(),
                'pending_leave_days' => LeaveRequest::whereIn('approval_status', ['submitted', 'approved'])->sum('requested_days'),
                'pending_overtime_hours' => OvertimeRequest::whereIn('approval_status', ['submitted', 'approved'])->sum('requested_hours'),
                'high_risk' => $predictions->whereIn('risk_level', ['high', 'critical'])->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAnalytics();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'prediction_number' => ['required', 'string', 'max:80', 'unique:leave_overtime_capacity_predictions,prediction_number'],
            'prediction_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:120'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'planned_headcount' => ['required', 'integer', 'min:0', 'max:100000'],
            'minimum_required_headcount' => ['required', 'integer', 'min:0', 'max:100000'],
            'forecast_leave_days' => ['required', 'numeric', 'min:0'],
            'forecast_overtime_hours' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $period = CarbonPeriod::create($validated['period_start'], $validated['period_end']);
        $workDays = collect($period)->filter(fn ($date) => ! $date->isWeekend())->count();
        $plannedHeadcount = (int) $validated['planned_headcount'];
        $minimumHeadcount = (int) $validated['minimum_required_headcount'];
        $periodStart = $validated['period_start'];
        $periodEnd = $validated['period_end'];

        $leaveQuery = LeaveRequest::query()
            ->whereIn('approval_status', ['submitted', 'approved'])
            ->whereDate('start_date', '<=', $periodEnd)
            ->whereDate('end_date', '>=', $periodStart)
            ->when($validated['company_id'] ?? null, fn ($query, $companyId) => $query->whereHas('employeeProfile', fn ($employee) => $employee->where('company_id', $companyId)))
            ->when($validated['branch_id'] ?? null, fn ($query, $branchId) => $query->whereHas('employeeProfile', fn ($employee) => $employee->where('branch_id', $branchId)))
            ->when($validated['department'] ?? null, fn ($query, $department) => $query->whereHas('employeeProfile', fn ($employee) => $employee->where('department', $department)));

        $overtimeQuery = OvertimeRequest::query()
            ->whereIn('approval_status', ['submitted', 'approved'])
            ->whereBetween('overtime_date', [$periodStart, $periodEnd])
            ->when($validated['company_id'] ?? null, fn ($query, $companyId) => $query->whereHas('employeeProfile', fn ($employee) => $employee->where('company_id', $companyId)))
            ->when($validated['branch_id'] ?? null, fn ($query, $branchId) => $query->whereHas('employeeProfile', fn ($employee) => $employee->where('branch_id', $branchId)))
            ->when($validated['department'] ?? null, fn ($query, $department) => $query->whereHas('employeeProfile', fn ($employee) => $employee->where('department', $department)));

        $existingLeaveDays = (float) $leaveQuery->sum('requested_days');
        $existingOvertimeHours = (float) $overtimeQuery->sum('requested_hours');
        $forecastLeaveDays = (float) $validated['forecast_leave_days'];
        $forecastOvertimeHours = (float) $validated['forecast_overtime_hours'];
        $estimatedCapacityDays = $plannedHeadcount * $workDays;
        $projectedAvailableDays = max(0, $estimatedCapacityDays - $existingLeaveDays - $forecastLeaveDays);
        $minimumRequiredDays = $minimumHeadcount * $workDays;
        $capacityGapDays = max(0, $minimumRequiredDays - $projectedAvailableDays);
        $overtimeNeededHours = round($capacityGapDays * 8, 2);
        $riskScore = $minimumRequiredDays > 0
            ? min(100, round(($capacityGapDays / $minimumRequiredDays) * 100, 2))
            : 0;
        $riskLevel = match (true) {
            $riskScore >= 25 => 'critical',
            $riskScore >= 15 => 'high',
            $riskScore >= 8 => 'medium',
            default => 'low',
        };

        $recommendations = [
            'work_days' => $workDays,
            'minimum_required_days' => $minimumRequiredDays,
            'total_leave_days' => $existingLeaveDays + $forecastLeaveDays,
            'total_overtime_hours' => $existingOvertimeHours + $forecastOvertimeHours,
            'actions' => $this->buildActions($riskLevel, $capacityGapDays, $overtimeNeededHours),
        ];

        $prediction = LeaveOvertimeCapacityPrediction::create([
            ...$validated,
            'existing_leave_days' => round($existingLeaveDays, 2),
            'existing_overtime_hours' => round($existingOvertimeHours, 2),
            'estimated_capacity_days' => round($estimatedCapacityDays, 2),
            'projected_available_days' => round($projectedAvailableDays, 2),
            'capacity_gap_days' => round($capacityGapDays, 2),
            'overtime_needed_hours' => $overtimeNeededHours,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'recommendation_snapshot' => $recommendations,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('leave_overtime_capacity_prediction.created', "Prediksi kapasitas {$prediction->prediction_number} dibuat.", $prediction, [], $prediction->toArray(), $request);

        return redirect()->route('analytics.leave-overtime-capacity')->with('success', 'Prediksi kapasitas cuti dan lembur berhasil dibuat.');
    }

    private function buildActions(string $riskLevel, float $capacityGapDays, float $overtimeNeededHours): array
    {
        if ($riskLevel === 'low') {
            return ['Kapasitas aman. Monitor approval cuti dan lembur berjalan seperti biasa.'];
        }

        $actions = [
            'Review cuti di periode ini sebelum approval final.',
            'Susun redistribusi shift untuk area dengan gap kapasitas.',
        ];

        if ($capacityGapDays > 0) {
            $actions[] = 'Siapkan coverage setara ' . number_format($capacityGapDays, 1, ',', '.') . ' hari kerja.';
        }

        if ($overtimeNeededHours > 0) {
            $actions[] = 'Estimasi kebutuhan lembur tambahan ' . number_format($overtimeNeededHours, 1, ',', '.') . ' jam.';
        }

        return $actions;
    }

    private function authorizeAnalytics(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance') || $user?->hasPermission('manage modules'), 403);
    }
}
