<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\PayrollAdjustment;
use App\Models\PayrollRun;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage payroll settings');

        $period = $request->string('period', now()->format('Y-m'))->toString();
        $status = $request->string('status')->toString();

        $adjustments = PayrollAdjustment::with(['employeeProfile', 'company', 'payrollRun'])
            ->when($period !== '', fn ($query) => $query->where('posting_period', $period))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->limit(120)
            ->get();

        return view('payroll.adjustments', [
            'active' => 'payroll-adjustments',
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('full_name')->limit(150)->get(),
            'payrollRuns' => PayrollRun::latest()->limit(60)->get(),
            'adjustments' => $adjustments,
            'filters' => ['period' => $period, 'status' => $status],
            'summary' => [
                'total' => $adjustments->count(),
                'approved' => $adjustments->where('status', 'approved')->count(),
                'posted' => $adjustments->where('status', 'posted')->count(),
                'net' => (float) $adjustments->sum(fn ($item) => $item->component_direction === 'deduction' ? -$item->adjustment_amount : $item->adjustment_amount),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'payroll_run_id' => ['nullable', 'exists:payroll_runs,id'],
            'adjustment_number' => ['required', 'string', 'max:80', 'unique:payroll_adjustments,adjustment_number'],
            'adjustment_type' => ['required', Rule::in(['retroactive_salary', 'backpay', 'correction', 'deduction_reversal', 'manual_adjustment'])],
            'component_code' => ['required', 'string', 'max:80'],
            'component_name' => ['required', 'string', 'max:255'],
            'component_direction' => ['required', Rule::in(['earning', 'deduction'])],
            'period_from' => ['required', 'date_format:Y-m'],
            'period_to' => ['required', 'date_format:Y-m'],
            'posting_period' => ['required', 'date_format:Y-m'],
            'base_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'adjustment_amount' => ['required', 'numeric', 'min:1', 'max:999999999999'],
            'tax_treatment' => ['required', Rule::in(['regular_taxable', 'irregular_taxable', 'non_taxable', 'deduction_adjustment'])],
            'status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'posted', 'rejected'])],
            'reason' => ['nullable', 'string', 'max:1500'],
            'audit_notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $employee = EmployeeProfile::findOrFail($data['employee_profile_id']);
        $taxable = in_array($data['tax_treatment'], ['regular_taxable', 'irregular_taxable'], true) ? (float) $data['adjustment_amount'] : 0;

        $adjustment = PayrollAdjustment::create([
            ...$data,
            'company_id' => $employee->company_id,
            'base_amount' => $data['base_amount'] ?? 0,
            'taxable_amount' => $taxable,
            'current_approver' => in_array($data['status'], ['approved', 'posted'], true) ? null : 'Payroll Reviewer',
            'approved_at' => in_array($data['status'], ['approved', 'posted'], true) ? now() : null,
            'approved_by' => in_array($data['status'], ['approved', 'posted'], true) ? auth()->user()?->name : null,
            'posted_at' => $data['status'] === 'posted' ? now() : null,
            'impact_snapshot' => $this->impactSnapshot($data, $taxable),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('payroll_adjustment.created', "Penyesuaian payroll {$adjustment->adjustment_number} dibuat.", $adjustment, [], $adjustment->toArray(), $request);

        return redirect()->route('payroll.adjustments')->with('success', 'Penyesuaian payroll berhasil disimpan.');
    }

    public function approve(Request $request, PayrollAdjustment $adjustment): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'posted', 'rejected'])],
            'audit_notes' => ['nullable', 'string', 'max:1500'],
        ]);

        $before = $adjustment->toArray();
        $adjustment->status = $data['decision'];
        $adjustment->audit_notes = $data['audit_notes'] ?? $adjustment->audit_notes;
        $adjustment->current_approver = null;
        $adjustment->approved_at = in_array($data['decision'], ['approved', 'posted'], true) ? now() : $adjustment->approved_at;
        $adjustment->approved_by = in_array($data['decision'], ['approved', 'posted'], true) ? auth()->user()?->name : $adjustment->approved_by;
        $adjustment->posted_at = $data['decision'] === 'posted' ? now() : $adjustment->posted_at;
        $adjustment->save();

        AuditLogger::record('payroll_adjustment.reviewed', "Penyesuaian payroll {$adjustment->adjustment_number} {$data['decision']}.", $adjustment, $before, $adjustment->toArray(), $request);

        return redirect()->route('payroll.adjustments')->with('success', 'Status penyesuaian payroll berhasil diperbarui.');
    }

    private function impactSnapshot(array $data, float $taxable): array
    {
        $amount = (float) $data['adjustment_amount'];
        $netImpact = $data['component_direction'] === 'deduction' ? -$amount : $amount;

        return [
            'gross_impact' => $data['component_direction'] === 'earning' ? $amount : 0,
            'deduction_impact' => $data['component_direction'] === 'deduction' ? $amount : 0,
            'taxable_impact' => $taxable,
            'net_impact' => $netImpact,
            'posting_period' => $data['posting_period'],
            'source_period' => $data['period_from'].' sampai '.$data['period_to'],
        ];
    }
}
