<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLoan;
use App\Models\EmployeeLoanInstallment;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollLoanController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage payroll settings');

        $status = $request->string('status')->toString();
        $loans = EmployeeLoan::with(['employeeProfile', 'company', 'branch', 'installments'])
            ->when($status !== '', fn ($query) => $query->where('approval_status', $status))
            ->latest('loan_date')
            ->limit(120)
            ->get();

        return view('payroll.loans', [
            'active' => 'payroll-loans',
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('full_name')->limit(150)->get(),
            'loans' => $loans,
            'filters' => ['status' => $status],
            'summary' => [
                'total' => $loans->count(),
                'approved' => $loans->where('approval_status', 'approved')->count(),
                'posting' => $loans->where('payroll_posting_status', 'active')->count(),
                'outstanding' => (float) $loans->sum('outstanding_amount'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'loan_number' => ['required', 'string', 'max:80', 'unique:employee_loans,loan_number'],
            'loan_type' => ['required', Rule::in(['employee_loan', 'cooperative_loan', 'cash_advance', 'other_deduction'])],
            'provider' => ['nullable', 'string', 'max:120'],
            'loan_date' => ['required', 'date'],
            'principal_amount' => ['required', 'numeric', 'min:1', 'max:999999999999'],
            'admin_fee' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'interest_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'tenor_months' => ['required', 'integer', 'min:1', 'max:120'],
            'start_period' => ['required', 'date'],
            'deduction_component_code' => ['nullable', 'string', 'max:80'],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'closed'])],
            'payroll_posting_status' => ['required', Rule::in(['not_posted', 'active', 'paused', 'completed'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $employee = EmployeeProfile::findOrFail($data['employee_profile_id']);
        $total = (float) $data['principal_amount'] + (float) ($data['admin_fee'] ?? 0) + (float) ($data['interest_amount'] ?? 0);
        $installment = round($total / (int) $data['tenor_months'], 2);
        $start = Carbon::parse($data['start_period'])->startOfMonth();

        $loan = DB::transaction(function () use ($data, $employee, $total, $installment, $start, $request) {
            $loan = EmployeeLoan::create([
                ...$data,
                'company_id' => $employee->company_id,
                'branch_id' => $employee->branch_id,
                'admin_fee' => $data['admin_fee'] ?? 0,
                'interest_amount' => $data['interest_amount'] ?? 0,
                'monthly_installment' => $installment,
                'paid_amount' => 0,
                'outstanding_amount' => $total,
                'current_approver' => $data['approval_status'] === 'approved' ? null : 'Payroll Reviewer',
                'end_period' => $start->copy()->addMonths((int) $data['tenor_months'] - 1)->endOfMonth()->toDateString(),
                'created_by' => auth()->id(),
            ]);

            for ($i = 1; $i <= (int) $data['tenor_months']; $i++) {
                EmployeeLoanInstallment::create([
                    'employee_loan_id' => $loan->id,
                    'installment_no' => $i,
                    'payroll_period' => $start->copy()->addMonths($i - 1)->format('Y-m'),
                    'due_date' => $start->copy()->addMonths($i - 1)->endOfMonth()->toDateString(),
                    'scheduled_amount' => $i === (int) $data['tenor_months'] ? $total - ($installment * ($i - 1)) : $installment,
                    'status' => 'scheduled',
                ]);
            }

            AuditLogger::record('employee_loan.created', "Pinjaman {$loan->loan_number} dibuat.", $loan, [], $loan->toArray(), $request);

            return $loan;
        });

        return redirect()->route('payroll.loans')->with('success', "Pinjaman {$loan->loan_number} berhasil dibuat.");
    }

    public function postInstallment(Request $request, EmployeeLoanInstallment $installment): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'status' => ['required', Rule::in(['scheduled', 'posted', 'skipped', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $before = $installment->load('loan')->toArray();
        $installment->fill($data);
        $installment->posted_at = $data['status'] === 'posted' ? now() : null;
        $installment->save();

        $loan = $installment->loan()->with('installments')->firstOrFail();
        $loan->paid_amount = (float) $loan->installments->where('status', 'posted')->sum('paid_amount');
        $loan->outstanding_amount = max(0, ((float) $loan->principal_amount + (float) $loan->admin_fee + (float) $loan->interest_amount) - (float) $loan->paid_amount);
        $loan->payroll_posting_status = $loan->outstanding_amount <= 0 ? 'completed' : $loan->payroll_posting_status;
        $loan->approval_status = $loan->outstanding_amount <= 0 ? 'closed' : $loan->approval_status;
        $loan->save();

        AuditLogger::record('employee_loan_installment.posted', "Cicilan {$loan->loan_number} periode {$installment->payroll_period} diperbarui.", $installment, $before, $installment->toArray(), $request);

        return redirect()->route('payroll.loans')->with('success', 'Cicilan pinjaman berhasil diperbarui.');
    }
}
