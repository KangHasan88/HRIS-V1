<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DataWarehouseExportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeIntegration();

        $companyId = $request->integer('company_id') ?: null;
        $branchId = $request->integer('branch_id') ?: null;
        $period = $request->input('period', now()->format('Y-m'));
        $format = $request->input('format', 'parquet');

        $datasets = $this->datasets($companyId, $branchId, $period);
        $qualityChecks = $this->qualityChecks($companyId, $branchId);
        $snapshotPlan = $this->snapshotPlan($datasets, $period, $format);

        return view('integrations.data-warehouse-export', [
            'active' => 'data-warehouse-export',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'filters' => [
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'period' => $period,
                'format' => $format,
            ],
            'summary' => [
                'datasets' => $datasets->count(),
                'ready' => $datasets->where('status', 'ready')->count(),
                'warning' => $datasets->where('status', 'warning')->count(),
                'rows' => $datasets->sum('rows'),
                'quality_issue' => $qualityChecks->where('status', 'warning')->count(),
            ],
            'datasets' => $datasets,
            'qualityChecks' => $qualityChecks,
            'snapshotPlan' => $snapshotPlan,
            'actions' => $this->actions($datasets, $qualityChecks),
        ]);
    }

    private function datasets(?int $companyId, ?int $branchId, string $period): Collection
    {
        return collect([
            $this->dataset('dim_employee', 'Employee master', 'employee_profiles', 'employee_number', $companyId, $branchId, 'Master karyawan, organisasi, status kerja, join/resign, jabatan.'),
            $this->dataset('dim_organization', 'Organization structure', 'organization_units', 'code', $companyId, $branchId, 'Company, cabang/site, unit organisasi, grade, cost center.'),
            $this->dataset('fact_attendance', 'Attendance events', 'attendance_clock_events', 'event_at', $companyId, $branchId, 'Clock-in/out, source device, status validasi, geofence/liveness.'),
            $this->dataset('fact_leave', 'Leave requests', 'leave_requests', 'submitted_at', $companyId, $branchId, 'Pengajuan cuti, saldo, approval, dan SLA cuti.'),
            $this->dataset('fact_overtime', 'Overtime requests', 'overtime_requests', 'submitted_at', $companyId, $branchId, 'Pengajuan lembur, jam request/approved, multiplier payroll.'),
            $this->dataset('fact_payroll_run', 'Payroll run summary', 'payroll_runs', 'period', $companyId, null, 'Run payroll, validasi, anomaly, gross/deduction/net estimate.'),
            $this->dataset('fact_payslip', 'Payslip published', 'payroll_payslips', 'period', $companyId, null, 'Slip gaji published, acknowledgement, resend, net pay.'),
            $this->dataset('fact_tax', 'PPh 21 monthly', 'tax_monthly_calculations', 'period', $companyId, null, 'PPh 21, TER, taxable income, komponen pajak bulanan.'),
            $this->dataset('fact_engagement', 'Engagement responses', 'engagement_survey_responses', 'submitted_at', $companyId, $branchId, 'Engagement score, eNPS, sentiment, anonymous/pulse survey.'),
            $this->dataset('fact_recruitment', 'Recruitment pipeline', 'recruitment_candidates', 'created_at', $companyId, $branchId, 'Candidate source, stage, screening score, status pipeline.'),
        ])->map(function (array $dataset) use ($period) {
            $dataset['partition'] = $period;
            $dataset['status'] = $dataset['rows'] > 0 ? 'ready' : 'warning';
            $dataset['status_label'] = $dataset['status'] === 'ready' ? 'Siap Export' : 'Belum Ada Data';
            $dataset['freshness'] = $dataset['last_updated'] ? now()->diffForHumans($dataset['last_updated'], true).' lalu' : '-';

            return $dataset;
        });
    }

    private function dataset(string $code, string $name, string $table, string $watermarkColumn, ?int $companyId, ?int $branchId, string $description): array
    {
        if (! Schema::hasTable($table)) {
            return [
                'code' => $code,
                'name' => $name,
                'table' => $table,
                'watermark' => $watermarkColumn,
                'rows' => 0,
                'last_updated' => null,
                'description' => $description,
            ];
        }

        $query = DB::table($table);

        if ($companyId && Schema::hasColumn($table, 'company_id')) {
            $query->where('company_id', $companyId);
        }

        if ($branchId && Schema::hasColumn($table, 'branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if (($companyId || $branchId) && Schema::hasColumn($table, 'employee_profile_id') && Schema::hasTable('employee_profiles')) {
            $employeeIds = DB::table('employee_profiles')
                ->when($companyId, fn ($employees) => $employees->where('company_id', $companyId))
                ->when($branchId, fn ($employees) => $employees->where('branch_id', $branchId))
                ->pluck('id');
            $query->whereIn('employee_profile_id', $employeeIds);
        }

        $rows = (clone $query)->count();
        $lastUpdated = Schema::hasColumn($table, 'updated_at')
            ? (clone $query)->max('updated_at')
            : (Schema::hasColumn($table, 'created_at') ? (clone $query)->max('created_at') : null);

        return [
            'code' => $code,
            'name' => $name,
            'table' => $table,
            'watermark' => $watermarkColumn,
            'rows' => $rows,
            'last_updated' => $lastUpdated ? \Carbon\Carbon::parse($lastUpdated) : null,
            'description' => $description,
        ];
    }

    private function qualityChecks(?int $companyId, ?int $branchId): Collection
    {
        $employeeQuery = DB::table('employee_profiles')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId));

        $missingNik = Schema::hasTable('employee_profiles') ? (clone $employeeQuery)->whereNull('employee_number')->count() : 0;
        $missingCompany = Schema::hasTable('employee_profiles') ? (clone $employeeQuery)->whereNull('company_id')->count() : 0;
        $pendingPayroll = Schema::hasTable('payroll_runs') ? DB::table('payroll_runs')->whereIn('status', ['draft', 'blocked'])->count() : 0;
        $failedConnector = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->where('failed_at', '>=', now()->subDays(7))->count() : 0;

        return collect([
            ['label' => 'Employee number kosong', 'value' => $missingNik, 'status' => $missingNik > 0 ? 'warning' : 'ready', 'note' => 'Wajib bersih untuk dim_employee.'],
            ['label' => 'Company kosong', 'value' => $missingCompany, 'status' => $missingCompany > 0 ? 'warning' : 'ready', 'note' => 'Dibutuhkan untuk filter tenant/company.'],
            ['label' => 'Payroll belum final', 'value' => $pendingPayroll, 'status' => $pendingPayroll > 0 ? 'warning' : 'ready', 'note' => 'Snapshot payroll BI sebaiknya pakai run final.'],
            ['label' => 'Failed job integrasi', 'value' => $failedConnector, 'status' => $failedConnector > 0 ? 'warning' : 'ready', 'note' => 'Pastikan queue sehat sebelum scheduled export.'],
        ]);
    }

    private function snapshotPlan(Collection $datasets, string $period, string $format): array
    {
        return [
            'snapshot_id' => 'HRIS-BI-'.$period.'-'.now()->format('His'),
            'format' => strtoupper($format),
            'schedule' => 'Harian 02:00 WIB, monthly freeze H+1 payroll closing',
            'destination' => 's3://kurmigo-warehouse/hris/'.$period.'/',
            'estimated_files' => $datasets->count(),
            'estimated_rows' => $datasets->sum('rows'),
            'manifest' => $datasets->map(fn (array $dataset) => [
                'dataset' => $dataset['code'],
                'path' => 'hris/'.$period.'/'.$dataset['code'].'.'.$format,
                'rows' => $dataset['rows'],
                'watermark' => $dataset['watermark'],
            ]),
        ];
    }

    private function actions(Collection $datasets, Collection $qualityChecks): Collection
    {
        $actions = collect();

        if ($datasets->where('status', 'warning')->isNotEmpty()) {
            $actions->push('Review dataset kosong sebelum mengaktifkan schedule BI production.');
        }

        if ($qualityChecks->where('status', 'warning')->isNotEmpty()) {
            $actions->push('Bereskan quality issue agar snapshot tidak menghasilkan angka BI yang bias.');
        }

        $actions->push('Lock format dan destination warehouse sebelum integrasi ke scheduler/worker.');
        $actions->push('Tambahkan checksum dan audit log ketika export fisik mulai diaktifkan.');

        return $actions->unique()->values();
    }

    private function authorizeIntegration(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage modules') || $user?->hasPermission('view delivery progress'), 403);
    }
}
