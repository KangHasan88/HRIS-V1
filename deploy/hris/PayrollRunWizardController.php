<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\EmployeeSalaryAssignment;
use App\Models\PayrollPayslip;
use App\Models\PayrollRun;
use App\Models\PayrollRunAnomaly;
use App\Models\PayrollRunValidation;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollRunWizardController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $runs = PayrollRun::with(['company', 'validations', 'anomalies.employee'])
            ->latest()
            ->limit(80)
            ->get();

        return view('payroll.run-wizard', [
            'active' => 'payroll-run-wizard',
            'companies' => Company::orderBy('code')->get(),
            'runs' => $runs,
            'summary' => [
                'runs' => PayrollRun::count(),
                'ready' => PayrollRun::where('status', 'ready')->count(),
                'blocked' => PayrollRun::where('status', 'blocked')->count(),
                'anomalies' => PayrollRunAnomaly::where('status', 'open')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'period' => ['required', 'date_format:Y-m'],
            'cutoff_start' => ['required', 'date'],
            'cutoff_end' => ['required', 'date', 'after_or_equal:cutoff_start'],
            'run_type' => ['required', Rule::in(['regular', 'thr', 'bonus', 'correction', 'final_pay'])],
            'notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $company = $data['company_id'] ? Company::find($data['company_id']) : null;
        $run = PayrollRun::create([
            ...$data,
            'run_number' => 'RUN-' . str_replace('-', '', $data['period']) . '-' . strtoupper($company?->code ?? 'ALL') . '-' . now()->format('His'),
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('payroll_run.created', "Payroll run {$run->run_number} dibuat.", $run, [], $run->toArray(), $request);

        return redirect()->route('payroll.run-wizard')->with('success', 'Payroll run berhasil dibuat. Jalankan validasi awal sebelum proses payroll.');
    }

    public function validateRun(Request $request, PayrollRun $payrollRun): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        DB::transaction(function () use ($request, $payrollRun): void {
            $payrollRun->validations()->delete();
            $payrollRun->anomalies()->delete();

            $employees = EmployeeProfile::query()
                ->where('is_active', true)
                ->when($payrollRun->company_id, fn ($query) => $query->where('company_id', $payrollRun->company_id))
                ->get();

            $assignments = EmployeeSalaryAssignment::query()
                ->whereIn('employee_profile_id', $employees->pluck('id'))
                ->where('approval_status', 'approved')
                ->get()
                ->keyBy('employee_profile_id');

            $missingSalary = $employees->filter(fn ($employee) => ! $assignments->has($employee->id));
            $missingBank = $employees->filter(fn ($employee) => ! $employee->bankAccounts()->exists());
            $missingBpjs = $employees->filter(fn ($employee) => ! $employee->bpjsProfile()->exists());
            $missingTax = $employees->filter(fn ($employee) => ! $employee->tax_profile_id);

            $checks = [
                ['SALARY_ASSIGNMENT', 'Assignment kompensasi approved', 'critical', $missingSalary, 'Karyawan belum punya salary assignment approved.'],
                ['BANK_ACCOUNT', 'Rekening bank aktif', 'critical', $missingBank, 'Karyawan belum punya rekening bank untuk pembayaran gaji.'],
                ['BPJS_PROFILE', 'Profil BPJS', 'warning', $missingBpjs, 'Profil BPJS belum lengkap; cek komponen BPJS sebelum final payroll.'],
                ['TAX_PROFILE', 'Profil PPh 21', 'warning', $missingTax, 'Profil pajak belum link; cek risiko perhitungan PPh 21.'],
            ];

            $failed = 0;
            foreach ($checks as [$code, $name, $severity, $affected, $message]) {
                $count = $affected->count();
                $status = $count > 0 ? 'failed' : 'passed';
                $failed += $status === 'failed' ? 1 : 0;
                PayrollRunValidation::create([
                    'payroll_run_id' => $payrollRun->id,
                    'check_code' => $code,
                    'check_name' => $name,
                    'severity' => $severity,
                    'status' => $status,
                    'affected_count' => $count,
                    'message' => $message,
                    'sample_payload' => $affected->take(5)->map(fn ($employee) => [
                        'employee_number' => $employee->employee_number,
                        'full_name' => $employee->full_name,
                    ])->values()->all(),
                ]);
            }

            $gross = 0;
            $deduction = 0;
            foreach ($employees as $employee) {
                $assignment = $assignments->get($employee->id);
                if (! $assignment) {
                    continue;
                }

                $current = (float) $assignment->base_salary + (float) $assignment->fixed_allowance + (float) $assignment->variable_allowance;
                $gross += $current;
                $previous = PayrollPayslip::where('employee_profile_id', $employee->id)->latest('period')->value('gross_pay') ?: 0;
                $variance = $previous > 0 ? (($current - (float) $previous) / (float) $previous) * 100 : 0;

                if (abs($variance) >= 30) {
                    PayrollRunAnomaly::create([
                        'payroll_run_id' => $payrollRun->id,
                        'employee_profile_id' => $employee->id,
                        'anomaly_code' => 'GROSS_VARIANCE',
                        'anomaly_name' => 'Perubahan gross pay signifikan',
                        'severity' => abs($variance) >= 50 ? 'high' : 'medium',
                        'previous_amount' => $previous,
                        'current_amount' => $current,
                        'variance_percent' => $variance,
                        'message' => 'Gross pay berubah lebih dari threshold 30% dibanding slip sebelumnya.',
                        'evidence_payload' => [
                            'base_salary' => $assignment->base_salary,
                            'fixed_allowance' => $assignment->fixed_allowance,
                            'variable_allowance' => $assignment->variable_allowance,
                        ],
                    ]);
                }
            }

            $payrollRun->forceFill([
                'employee_count' => $employees->count(),
                'validation_passed' => PayrollRunValidation::where('payroll_run_id', $payrollRun->id)->where('status', 'passed')->count(),
                'validation_failed' => PayrollRunValidation::where('payroll_run_id', $payrollRun->id)->where('status', 'failed')->count(),
                'anomaly_count' => PayrollRunAnomaly::where('payroll_run_id', $payrollRun->id)->count(),
                'gross_estimate' => $gross,
                'deduction_estimate' => $deduction,
                'net_estimate' => max(0, $gross - $deduction),
                'status' => $failed > 0 ? 'blocked' : 'ready',
                'validated_at' => now(),
                'source_snapshot' => [
                    'employee_count' => $employees->count(),
                    'approved_salary_assignments' => $assignments->count(),
                    'validation_threshold' => 'gross variance >= 30%',
                ],
            ])->save();

            AuditLogger::record('payroll_run.validated', "Payroll run {$payrollRun->run_number} divalidasi.", $payrollRun, [], $payrollRun->fresh()->toArray(), $request);
        });

        return redirect()->route('payroll.run-wizard')->with('success', 'Validasi awal payroll selesai.');
    }
}
