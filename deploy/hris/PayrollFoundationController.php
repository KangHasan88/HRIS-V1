<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EmployeeSalaryAssignment;
use App\Models\ManagerClaimRequest;
use App\Models\PayrollAdjustment;
use App\Models\PayrollBpjsProfile;
use App\Models\PayrollComponent;
use App\Models\PayrollDisbursementBatch;
use App\Models\PayrollPayslip;
use App\Models\PayrollRun;
use App\Models\PayrollSalaryBand;
use App\Models\TaxProfile;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PayrollFoundationController extends Controller
{
    public function index(): View
    {
        $employeeCount = EmployeeProfile::where('is_active', true)->count();
        $salaryAssignments = EmployeeSalaryAssignment::where('approval_status', 'approved')->count();
        $components = PayrollComponent::where('status', 'active')->count();
        $salaryBands = PayrollSalaryBand::where('status', 'active')->count();
        $bpjsProfiles = PayrollBpjsProfile::where('is_active', true)->count();
        $taxProfiles = TaxProfile::where('is_active', true)->count();
        $latestRuns = PayrollRun::with('company')->latest()->limit(8)->get();
        $latestDisbursements = PayrollDisbursementBatch::with('company')->latest()->limit(8)->get();
        $claimQuery = ManagerClaimRequest::whereIn('approval_status', ['submitted', 'pending', 'approved']);
        if (Schema::hasColumn('manager_claim_requests', 'payroll_handoff_status')) {
            $claimQuery->where(function ($query) {
                $query->whereNull('payroll_handoff_status')
                    ->orWhereIn('payroll_handoff_status', ['pending', 'ready']);
            });
        }
        $pendingClaims = $claimQuery->count();
        $pendingAdjustments = PayrollAdjustment::whereIn('status', ['draft', 'submitted'])->count();
        $publishedPayslips = PayrollPayslip::where('status', 'published')->count();

        $readiness = collect([
            [
                'label' => 'Master karyawan aktif',
                'value' => $employeeCount,
                'target' => max(1, $employeeCount),
                'status' => $employeeCount > 0 ? 'ready' : 'attention',
                'route' => route('hris.employees'),
                'action' => 'Data Karyawan',
            ],
            [
                'label' => 'Assignment struktur gaji',
                'value' => $salaryAssignments,
                'target' => max(1, $employeeCount),
                'status' => $salaryAssignments >= $employeeCount && $employeeCount > 0 ? 'ready' : 'attention',
                'route' => route('payroll.salary-structures'),
                'action' => 'Struktur Gaji',
            ],
            [
                'label' => 'Komponen payroll aktif',
                'value' => $components,
                'target' => 4,
                'status' => $components >= 4 ? 'ready' : 'in_progress',
                'route' => route('payroll.components'),
                'action' => 'Komponen',
            ],
            [
                'label' => 'Salary band aktif',
                'value' => $salaryBands,
                'target' => 1,
                'status' => $salaryBands > 0 ? 'ready' : 'attention',
                'route' => route('payroll.salary-structures'),
                'action' => 'Salary Band',
            ],
            [
                'label' => 'Profil BPJS aktif',
                'value' => $bpjsProfiles,
                'target' => max(1, $employeeCount),
                'status' => $bpjsProfiles >= $employeeCount && $employeeCount > 0 ? 'ready' : 'in_progress',
                'route' => route('payroll.bpjs-profiles'),
                'action' => 'BPJS',
            ],
            [
                'label' => 'Profil PPh 21 aktif',
                'value' => $taxProfiles,
                'target' => max(1, $employeeCount),
                'status' => $taxProfiles >= $employeeCount && $employeeCount > 0 ? 'ready' : 'in_progress',
                'route' => route('payroll.tax-profiles'),
                'action' => 'PPh 21',
            ],
        ]);

        $readyCount = $readiness->where('status', 'ready')->count();
        $score = (int) round(($readyCount / max(1, $readiness->count())) * 100);

        return view('payroll.foundation', [
            'active' => 'payroll',
            'readiness' => $readiness,
            'latestRuns' => $latestRuns,
            'latestDisbursements' => $latestDisbursements,
            'summary' => [
                'score' => $score,
                'employee_count' => $employeeCount,
                'ready_count' => $readyCount,
                'readiness_total' => $readiness->count(),
                'pending_claims' => $pendingClaims,
                'pending_adjustments' => $pendingAdjustments,
                'published_payslips' => $publishedPayslips,
                'latest_run_status' => $latestRuns->first()?->status ?: 'Belum ada run',
            ],
        ]);
    }
}
