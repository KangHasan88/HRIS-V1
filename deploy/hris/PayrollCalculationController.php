<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EmployeeSalaryAssignment;
use App\Models\PayrollAdjustment;
use App\Models\PayrollBpjsProfile;
use App\Models\PayrollRun;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PayrollCalculationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage payroll settings');

        $runs = PayrollRun::with('company')
            ->latest()
            ->limit(80)
            ->get();

        $selectedRun = $request->integer('run_id')
            ? PayrollRun::with('company')->find($request->integer('run_id'))
            : $runs->first();

        $preview = $selectedRun ? $this->buildPreview($selectedRun) : collect();

        return view('payroll.calculation', [
            'active' => 'payroll-calculation',
            'runs' => $runs,
            'selectedRun' => $selectedRun,
            'preview' => $preview,
            'summary' => $this->summarize($preview),
        ]);
    }

    public function calculate(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $preview = $this->buildPreview($payrollRun);
        $summary = $this->summarize($preview);

        $payrollRun->forceFill([
            'employee_count' => $preview->count(),
            'gross_estimate' => $summary['gross'],
            'deduction_estimate' => $summary['deduction'],
            'net_estimate' => $summary['net'],
            'status' => $summary['blocked'] > 0 ? 'blocked' : 'calculated',
            'source_snapshot' => [
                'calculated_at' => now()->toDateTimeString(),
                'formula' => 'base + allowance + adjustment - BPJS karyawan - estimasi PPh21',
                'employee_count' => $preview->count(),
                'blocked_count' => $summary['blocked'],
                'notes' => 'Preview payroll; final slip tetap menunggu approval dan publish.',
            ],
            'validated_at' => $payrollRun->validated_at ?: now(),
        ])->save();

        AuditLogger::record('payroll_run.calculated', "Payroll run {$payrollRun->run_number} dihitung.", $payrollRun, [], $payrollRun->fresh()->toArray(), $request);

        return redirect()
            ->route('payroll.calculation', ['run_id' => $payrollRun->id])
            ->with('success', 'Perhitungan payroll selesai. Cek preview sebelum lanjut ke slip gaji dan pencairan.');
    }

    private function buildPreview(PayrollRun $run): Collection
    {
        $employees = EmployeeProfile::with(['company', 'branch', 'taxProfile'])
            ->where('is_active', true)
            ->when($run->company_id, fn ($query) => $query->where('company_id', $run->company_id))
            ->orderBy('employee_number')
            ->limit(120)
            ->get();

        $assignments = EmployeeSalaryAssignment::whereIn('employee_profile_id', $employees->pluck('id'))
            ->where('approval_status', 'approved')
            ->whereDate('effective_start', '<=', $run->cutoff_end ?? now())
            ->where(function ($query) use ($run) {
                $query->whereNull('effective_end')
                    ->orWhereDate('effective_end', '>=', $run->cutoff_start ?? now());
            })
            ->latest('effective_start')
            ->get()
            ->unique('employee_profile_id')
            ->keyBy('employee_profile_id');

        $bpjsProfiles = PayrollBpjsProfile::whereIn('employee_profile_id', $employees->pluck('id'))
            ->where('is_active', true)
            ->latest('effective_date')
            ->get()
            ->unique('employee_profile_id')
            ->keyBy('employee_profile_id');

        $adjustments = PayrollAdjustment::whereIn('employee_profile_id', $employees->pluck('id'))
            ->where('posting_period', $run->period)
            ->whereIn('status', ['approved', 'posted'])
            ->get()
            ->groupBy('employee_profile_id');

        return $employees->map(function (EmployeeProfile $employee) use ($assignments, $bpjsProfiles, $adjustments) {
            $assignment = $assignments->get($employee->id);
            $bpjs = $bpjsProfiles->get($employee->id);
            $employeeAdjustments = $adjustments->get($employee->id, collect());

            $base = (float) ($assignment?->base_salary ?? 0);
            $fixed = (float) ($assignment?->fixed_allowance ?? 0);
            $variable = (float) ($assignment?->variable_allowance ?? 0);
            $earningAdjustment = (float) $employeeAdjustments
                ->where('component_direction', 'earning')
                ->sum('adjustment_amount');
            $deductionAdjustment = (float) $employeeAdjustments
                ->where('component_direction', 'deduction')
                ->sum('adjustment_amount');

            $gross = $base + $fixed + $variable + $earningAdjustment;
            $bpjsEmployee = $this->bpjsEmployeeAmount($bpjs, $gross);
            $bpjsEmployer = $this->bpjsEmployerAmount($bpjs, $gross);
            $pph21 = $this->monthlyPph21Estimate($gross - $bpjsEmployee, $employee->taxProfile?->ptkp_status);
            $deduction = $bpjsEmployee + $pph21 + $deductionAdjustment;
            $net = max(0, $gross - $deduction);

            $issues = collect([
                $assignment ? null : 'Belum ada struktur gaji approved',
                $employee->taxProfile ? null : 'Belum link profil PPh 21',
                $bpjs ? null : 'Belum ada profil BPJS aktif',
            ])->filter()->values();

            return [
                'employee' => $employee,
                'base' => $base,
                'fixed' => $fixed,
                'variable' => $variable,
                'earning_adjustment' => $earningAdjustment,
                'deduction_adjustment' => $deductionAdjustment,
                'gross' => $gross,
                'bpjs_employee' => $bpjsEmployee,
                'bpjs_employer' => $bpjsEmployer,
                'pph21' => $pph21,
                'deduction' => $deduction,
                'net' => $net,
                'status' => $issues->isEmpty() ? 'Siap' : 'Perlu cek',
                'issues' => $issues,
            ];
        });
    }

    private function summarize(Collection $preview): array
    {
        return [
            'employees' => $preview->count(),
            'gross' => $preview->sum('gross'),
            'deduction' => $preview->sum('deduction'),
            'net' => $preview->sum('net'),
            'bpjs_employee' => $preview->sum('bpjs_employee'),
            'bpjs_employer' => $preview->sum('bpjs_employer'),
            'pph21' => $preview->sum('pph21'),
            'blocked' => $preview->where('status', 'Perlu cek')->count(),
        ];
    }

    private function bpjsEmployeeAmount(?PayrollBpjsProfile $profile, float $gross): float
    {
        if (! $profile) {
            return 0;
        }

        $healthBase = (float) ($profile->health_contribution_base ?: $gross);
        $employmentBase = (float) ($profile->employment_contribution_base ?: $gross);

        return round(
            ($healthBase * (float) $profile->health_employee_rate / 100)
            + ($employmentBase * (float) $profile->jht_employee_rate / 100)
            + ($employmentBase * (float) $profile->jp_employee_rate / 100),
            2
        );
    }

    private function bpjsEmployerAmount(?PayrollBpjsProfile $profile, float $gross): float
    {
        if (! $profile) {
            return 0;
        }

        $healthBase = (float) ($profile->health_contribution_base ?: $gross);
        $employmentBase = (float) ($profile->employment_contribution_base ?: $gross);

        return round(
            ($healthBase * (float) $profile->health_employer_rate / 100)
            + ($employmentBase * (float) $profile->jht_employer_rate / 100)
            + ($employmentBase * (float) $profile->jp_employer_rate / 100),
            2
        );
    }

    private function monthlyPph21Estimate(float $monthlyTaxable, ?string $ptkpStatus): float
    {
        $annualNet = max(0, $monthlyTaxable * 12);
        $ptkp = match (strtoupper((string) $ptkpStatus)) {
            'K/3' => 72000000,
            'K/2' => 67500000,
            'K/1' => 63000000,
            'K/0' => 58500000,
            'TK/3' => 67500000,
            'TK/2' => 63000000,
            'TK/1' => 58500000,
            default => 54000000,
        };

        $taxable = max(0, $annualNet - $ptkp);
        $annualTax = 0;

        foreach ([[60000000, 0.05], [190000000, 0.15], [250000000, 0.25], [4500000000, 0.30]] as [$cap, $rate]) {
            if ($taxable <= 0) {
                break;
            }
            $slice = min($taxable, $cap);
            $annualTax += $slice * $rate;
            $taxable -= $slice;
        }

        if ($taxable > 0) {
            $annualTax += $taxable * 0.35;
        }

        return round($annualTax / 12, 2);
    }
}
