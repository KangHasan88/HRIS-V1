<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\PayrollPayslip;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PayrollPayslipController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $payslips = PayrollPayslip::with(['employee.company', 'company'])->latest()->limit(100)->get();

        return view('payroll.payslips', [
            'active' => 'payroll-payslips',
            'employees' => EmployeeProfile::with('company')->where('is_active', true)->orderBy('full_name')->get(),
            'payslips' => $payslips,
            'summary' => [
                'total' => PayrollPayslip::count(),
                'published' => PayrollPayslip::where('status', 'published')->count(),
                'acknowledged' => PayrollPayslip::whereNotNull('acknowledged_at')->count(),
                'resend' => PayrollPayslip::sum('resend_count'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'period' => ['required', 'date_format:Y-m'],
            'status' => ['required', 'in:draft,published'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'overtime' => ['nullable', 'numeric', 'min:0'],
            'bpjs_deduction' => ['nullable', 'numeric', 'min:0'],
            'pph21_deduction' => ['nullable', 'numeric', 'min:0'],
            'other_deduction' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $employee = EmployeeProfile::with('company')->findOrFail($data['employee_profile_id']);
        $earnings = [
            'Gaji Pokok' => (float) $data['basic_salary'],
            'Tunjangan' => (float) ($data['allowance'] ?? 0),
            'Lembur' => (float) ($data['overtime'] ?? 0),
        ];
        $deductions = [
            'BPJS' => (float) ($data['bpjs_deduction'] ?? 0),
            'PPh 21' => (float) ($data['pph21_deduction'] ?? 0),
            'Potongan Lain' => (float) ($data['other_deduction'] ?? 0),
        ];
        $gross = array_sum($earnings);
        $deduction = array_sum($deductions);
        $net = max(0, $gross - $deduction);
        $payload = [
            'period' => $data['period'],
            'employee_number' => $employee->employee_number,
            'employee_name' => $employee->full_name,
            'company_code' => $employee->company?->code,
            'earnings' => $earnings,
            'deductions' => $deductions,
            'net_pay' => $net,
            'notes' => $data['notes'] ?? null,
        ];

        $payslip = PayrollPayslip::updateOrCreate(
            [
                'employee_profile_id' => $employee->id,
                'period' => $data['period'],
            ],
            [
                'company_id' => $employee->company_id,
                'payslip_number' => 'SLIP-' . str_replace('-', '', $data['period']) . '-' . $employee->employee_number,
                'status' => $data['status'],
                'gross_pay' => $gross,
                'total_deduction' => $deduction,
                'net_pay' => $net,
                'earning_snapshot' => $earnings,
                'deduction_snapshot' => $deductions,
                'employee_snapshot' => [
                    'employee_number' => $employee->employee_number,
                    'full_name' => $employee->full_name,
                    'position' => $employee->position,
                    'department' => $employee->department,
                    'company' => $employee->company?->name,
                ],
                'checksum' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE)),
                'published_at' => $data['status'] === 'published' ? now() : null,
                'last_sent_at' => $data['status'] === 'published' ? now() : null,
                'created_by' => auth()->id(),
            ]
        );

        AuditLogger::record(
            'payroll_payslip.saved',
            "Slip gaji {$payslip->payslip_number} disimpan.",
            $payslip,
            [],
            $payslip->only(['payslip_number', 'period', 'status', 'gross_pay', 'net_pay']),
            $request
        );

        return redirect()->route('payroll.payslips')->with('success', 'Slip gaji berhasil disimpan.');
    }

    public function resend(Request $request, PayrollPayslip $payslip): RedirectResponse
    {
        Gate::authorize('manage payroll settings');
        abort_unless($payslip->status === 'published', 422);

        $payslip->increment('resend_count');
        $payslip->forceFill(['last_sent_at' => now()])->save();

        AuditLogger::record('payroll_payslip.resent', "Notifikasi slip gaji {$payslip->payslip_number} dikirim ulang.", $payslip, [], [
            'resend_count' => $payslip->resend_count,
            'last_sent_at' => $payslip->last_sent_at,
        ], $request);

        return redirect()->route('payroll.payslips')->with('success', 'Notifikasi slip gaji berhasil dikirim ulang.');
    }

    public function ess(): View
    {
        $employee = $this->currentEmployee();

        return view('ess.payslips', [
            'active' => 'ess-payslips',
            'employee' => $employee,
            'payslips' => $employee
                ? PayrollPayslip::where('employee_profile_id', $employee->id)->where('status', 'published')->latest()->get()
                : collect(),
        ]);
    }

    public function acknowledge(Request $request, PayrollPayslip $payslip): RedirectResponse
    {
        $employee = $this->currentEmployee();
        abort_unless($employee && $payslip->employee_profile_id === $employee->id && $payslip->status === 'published', 404);

        $payslip->forceFill([
            'acknowledged_at' => now(),
            'acknowledged_by' => auth()->id(),
        ])->save();

        AuditLogger::record('payroll_payslip.acknowledged', "Slip gaji {$payslip->payslip_number} di-acknowledge karyawan.", $payslip, [], [
            'acknowledged_at' => $payslip->acknowledged_at,
            'acknowledged_by' => auth()->id(),
        ], $request);

        return redirect()->route('ess.payslips')->with('success', 'Slip gaji berhasil dikonfirmasi.');
    }

    public function download(PayrollPayslip $payslip): Response
    {
        $employee = $this->currentEmployee();
        abort_unless($employee && $payslip->employee_profile_id === $employee->id && $payslip->status === 'published', 404);

        $payload = [
            'downloaded_at' => now()->timezone('Asia/Jakarta')->toIso8601String(),
            'payslip' => $payslip->only([
                'payslip_number', 'period', 'gross_pay', 'total_deduction', 'net_pay',
                'earning_snapshot', 'deduction_snapshot', 'employee_snapshot', 'checksum',
            ]),
        ];

        return response(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($payslip->payslip_number) . '.json"',
        ]);
    }

    private function currentEmployee(): ?EmployeeProfile
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return EmployeeProfile::where('user_id', $user->id)
            ->orWhere('work_email', $user->email)
            ->orWhere('full_name', $user->name)
            ->first();
    }
}
