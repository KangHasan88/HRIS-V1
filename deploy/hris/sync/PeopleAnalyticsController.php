<?php

namespace App\Http\Controllers;

use App\Models\AttendanceClockEvent;
use App\Models\Branch;
use App\Models\BusinessTripRequest;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\ExternalWorkforceWorker;
use App\Models\LeaveRequest;
use App\Models\ManagerClaimRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollPayslip;
use App\Models\PayrollRun;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PeopleAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAnalytics();

        $periodStart = Carbon::parse($request->input('start_date', now()->startOfYear()->toDateString()))->startOfDay();
        $periodEnd = Carbon::parse($request->input('end_date', now()->toDateString()))->endOfDay();
        $companyId = $request->integer('company_id') ?: null;
        $branchId = $request->integer('branch_id') ?: null;

        $employees = EmployeeProfile::with(['company', 'branch'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->get();

        $activeEmployees = $employees->where('is_active', true);
        $joined = $employees->filter(fn (EmployeeProfile $employee) => $employee->join_date && $employee->join_date->between($periodStart, $periodEnd));
        $ended = $employees->filter(fn (EmployeeProfile $employee) => ! $employee->is_active || ($employee->contract_end_date && $employee->contract_end_date->between($periodStart, $periodEnd)));

        $clockEvents = AttendanceClockEvent::with('employeeProfile')
            ->whereBetween('event_at', [$periodStart, $periodEnd])
            ->whereHas('employeeProfile', fn ($query) => $query
                ->when($companyId, fn ($scope) => $scope->where('company_id', $companyId))
                ->when($branchId, fn ($scope) => $scope->where('branch_id', $branchId)))
            ->get();

        $leaveRequests = LeaveRequest::with('employeeProfile')
            ->whereBetween('start_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('employeeProfile', fn ($query) => $query
                ->when($companyId, fn ($scope) => $scope->where('company_id', $companyId))
                ->when($branchId, fn ($scope) => $scope->where('branch_id', $branchId)))
            ->get();

        $overtimeRequests = OvertimeRequest::with('employeeProfile')
            ->whereBetween('overtime_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('employeeProfile', fn ($query) => $query
                ->when($companyId, fn ($scope) => $scope->where('company_id', $companyId))
                ->when($branchId, fn ($scope) => $scope->where('branch_id', $branchId)))
            ->get();

        $payrollRuns = PayrollRun::with('company')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where(function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('cutoff_start', [$periodStart->toDateString(), $periodEnd->toDateString()])
                    ->orWhereBetween('cutoff_end', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            })
            ->latest('cutoff_end')
            ->limit(12)
            ->get();

        $payslips = PayrollPayslip::with('employee')
            ->whereHas('employee', fn ($query) => $query
                ->when($companyId, fn ($scope) => $scope->where('company_id', $companyId))
                ->when($branchId, fn ($scope) => $scope->where('branch_id', $branchId)))
            ->where(function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('published_at', [$periodStart, $periodEnd])
                    ->orWhereNull('published_at');
            })
            ->get();

        $claimAmount = ManagerClaimRequest::whereIn('approval_status', ['submitted', 'approved'])
            ->whereBetween('claim_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('employeeProfile', fn ($query) => $query
                ->when($companyId, fn ($scope) => $scope->where('company_id', $companyId))
                ->when($branchId, fn ($scope) => $scope->where('branch_id', $branchId)))
            ->sum('amount');

        $businessTripCount = BusinessTripRequest::whereBetween('start_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereHas('employeeProfile', fn ($query) => $query
                ->when($companyId, fn ($scope) => $scope->where('company_id', $companyId))
                ->when($branchId, fn ($scope) => $scope->where('branch_id', $branchId)))
            ->count();

        $externalWorkers = ExternalWorkforceWorker::when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('status', 'active')
            ->count();

        $clockInCount = $clockEvents->where('event_type', 'clock_in')->count();
        $lateCount = $clockEvents
            ->where('event_type', 'clock_in')
            ->filter(fn (AttendanceClockEvent $event) => $event->event_at?->format('H:i') > '08:30')
            ->count();
        $trustedDeviceRate = $clockEvents->count() > 0 ? (int) round(($clockEvents->where('is_device_trusted', true)->count() / $clockEvents->count()) * 100) : 0;
        $turnoverRate = $activeEmployees->count() > 0 ? round(($ended->count() / max(1, $activeEmployees->count())) * 100, 1) : 0;

        return view('analytics.people-dashboard', [
            'active' => 'people-analytics',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'filters' => [
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            'summary' => [
                'active_employee' => $activeEmployees->count(),
                'joined' => $joined->count(),
                'ended' => $ended->count(),
                'turnover_rate' => $turnoverRate,
                'clock_in' => $clockInCount,
                'late_rate' => $clockInCount > 0 ? round(($lateCount / $clockInCount) * 100, 1) : 0,
                'leave_days' => round((float) $leaveRequests->sum('requested_days'), 1),
                'overtime_hours' => round((float) $overtimeRequests->sum('requested_hours'), 1),
                'payroll_cost' => (float) ($payrollRuns->sum('net_estimate') ?: $payslips->sum('net_pay')),
                'gross_pay' => (float) $payslips->sum('gross_pay'),
                'reimbursement' => (float) $claimAmount,
                'external_workers' => $externalWorkers,
                'business_trips' => $businessTripCount,
                'trusted_device_rate' => $trustedDeviceRate,
            ],
            'departmentBreakdown' => $this->departmentBreakdown($activeEmployees),
            'branchBreakdown' => $this->branchBreakdown($activeEmployees),
            'payrollRuns' => $payrollRuns,
            'attendanceTrend' => $this->attendanceTrend($clockEvents),
            'insights' => $this->insights($activeEmployees, $turnoverRate, $clockInCount, $lateCount, $trustedDeviceRate, $payrollRuns, $externalWorkers),
        ]);
    }

    private function departmentBreakdown(Collection $employees): Collection
    {
        return $employees
            ->groupBy(fn (EmployeeProfile $employee) => $employee->department ?: 'Belum diset')
            ->map(fn (Collection $items, string $department) => [
                'label' => $department,
                'value' => $items->count(),
                'percent' => $employees->count() > 0 ? (int) round(($items->count() / $employees->count()) * 100) : 0,
            ])
            ->sortByDesc('value')
            ->take(8)
            ->values();
    }

    private function branchBreakdown(Collection $employees): Collection
    {
        return $employees
            ->groupBy(fn (EmployeeProfile $employee) => $employee->branch?->code ?: 'Tanpa site')
            ->map(fn (Collection $items, string $branch) => [
                'label' => $branch,
                'value' => $items->count(),
                'percent' => $employees->count() > 0 ? (int) round(($items->count() / $employees->count()) * 100) : 0,
            ])
            ->sortByDesc('value')
            ->take(8)
            ->values();
    }

    private function attendanceTrend(Collection $clockEvents): Collection
    {
        return $clockEvents
            ->where('event_type', 'clock_in')
            ->groupBy(fn (AttendanceClockEvent $event) => $event->event_at?->format('d M') ?: '-')
            ->map(fn (Collection $items, string $date) => [
                'label' => $date,
                'clock_in' => $items->count(),
                'late' => $items->filter(fn (AttendanceClockEvent $event) => $event->event_at?->format('H:i') > '08:30')->count(),
            ])
            ->take(10)
            ->values();
    }

    private function insights(Collection $activeEmployees, float $turnoverRate, int $clockInCount, int $lateCount, int $trustedDeviceRate, Collection $payrollRuns, int $externalWorkers): array
    {
        return [
            [
                'title' => 'Headcount aktif',
                'body' => $activeEmployees->count() > 0
                    ? 'Basis headcount sudah tersedia untuk analisis payroll, absensi, dan perencanaan workforce.'
                    : 'Belum ada employee aktif pada filter ini, cek scope company/site.',
                'tone' => $activeEmployees->count() > 0 ? 'good' : 'risk',
            ],
            [
                'title' => 'Turnover periode',
                'body' => $turnoverRate > 5
                    ? "Turnover {$turnoverRate}% perlu review penyebab exit, kontrak berakhir, atau mutasi."
                    : "Turnover {$turnoverRate}% masih terkendali pada periode ini.",
                'tone' => $turnoverRate > 5 ? 'warning' : 'good',
            ],
            [
                'title' => 'Kedisiplinan absensi',
                'body' => $clockInCount > 0
                    ? $lateCount.' clock-in terindikasi melewati 08:30 dari '.$clockInCount.' clock-in.'
                    : 'Belum ada data clock-in pada periode ini.',
                'tone' => $lateCount > 0 ? 'warning' : 'neutral',
            ],
            [
                'title' => 'Kualitas device attendance',
                'body' => "Trusted device rate {$trustedDeviceRate}%. Di bawah 90% perlu review binding device dan mobile policy.",
                'tone' => $trustedDeviceRate >= 90 ? 'good' : 'warning',
            ],
            [
                'title' => 'Biaya payroll',
                'body' => $payrollRuns->count() > 0
                    ? 'Payroll run sudah menjadi sumber utama dashboard biaya periode ini.'
                    : 'Belum ada payroll run di periode ini; dashboard memakai payslip bila tersedia.',
                'tone' => $payrollRuns->count() > 0 ? 'good' : 'neutral',
            ],
            [
                'title' => 'Non-employee workforce',
                'body' => $externalWorkers > 0
                    ? "{$externalWorkers} worker outsourcing/borongan aktif ikut terlihat sebagai konteks kapasitas."
                    : 'Belum ada worker outsourcing/borongan aktif pada scope ini.',
                'tone' => $externalWorkers > 0 ? 'neutral' : 'good',
            ],
        ];
    }

    private function authorizeAnalytics(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance') || $user?->hasPermission('view delivery progress') || $user?->hasPermission('manage modules'), 403);
    }
}
