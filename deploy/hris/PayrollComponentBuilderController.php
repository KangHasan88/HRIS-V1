<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PayrollComponent;
use App\Models\PayrollComponentRule;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollComponentBuilderController extends Controller
{
    public function index(): View
    {
        $this->authorizePayroll();

        $components = PayrollComponent::with(['company', 'rules'])
            ->orderBy('sort_order')
            ->orderBy('component_code')
            ->limit(150)
            ->get();

        $rules = PayrollComponentRule::with('component')->latest()->limit(120)->get();

        return view('payroll.component-builder', [
            'active' => 'payroll-components',
            'companies' => Company::orderBy('code')->get(),
            'components' => $components,
            'rules' => $rules,
            'summary' => [
                'components' => $components->count(),
                'earnings' => $components->where('component_category', 'earning')->count(),
                'deductions' => $components->where('component_category', 'deduction')->count(),
                'active' => $components->where('status', 'active')->count(),
            ],
        ]);
    }

    public function storeComponent(Request $request): RedirectResponse
    {
        $this->authorizePayroll();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'component_code' => ['required', 'string', 'max:80', 'unique:payroll_components,component_code'],
            'component_name' => ['required', 'string', 'max:180'],
            'component_category' => ['required', Rule::in(['earning', 'deduction', 'benefit', 'employer_contribution', 'information'])],
            'calculation_method' => ['required', Rule::in(['fixed', 'formula', 'rate', 'attendance_based', 'manual'])],
            'default_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'rate_value' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'formula_expression' => ['nullable', 'string', 'max:2000'],
            'dependencies' => ['nullable', 'string', 'max:1000'],
            'prorate_basis' => ['required', Rule::in(['none', 'calendar_days', 'working_days', 'attendance_days', 'join_resign_days'])],
            'is_taxable' => ['nullable', 'boolean'],
            'is_bpjs_basis' => ['nullable', 'boolean'],
            'is_payslip_visible' => ['nullable', 'boolean'],
            'gl_account' => ['nullable', 'string', 'max:120'],
            'effective_start' => ['nullable', 'date'],
            'effective_end' => ['nullable', 'date', 'after_or_equal:effective_start'],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive', 'archived'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $component = PayrollComponent::create([
            ...collect($validated)->except('dependencies')->all(),
            'default_amount' => $validated['default_amount'] ?? 0,
            'rate_value' => $validated['rate_value'] ?? 0,
            'dependencies' => $this->splitList($validated['dependencies'] ?? null),
            'is_taxable' => (bool) ($validated['is_taxable'] ?? false),
            'is_bpjs_basis' => (bool) ($validated['is_bpjs_basis'] ?? false),
            'is_payslip_visible' => (bool) ($validated['is_payslip_visible'] ?? true),
            'sort_order' => $validated['sort_order'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('payroll_component.created', "Komponen payroll {$component->component_code} dibuat.", $component, [], $component->toArray(), $request);

        return redirect()->route('payroll.components')->with('success', 'Komponen payroll berhasil disimpan.');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $this->authorizePayroll();

        $validated = $request->validate([
            'payroll_component_id' => ['required', 'exists:payroll_components,id'],
            'rule_name' => ['required', 'string', 'max:180'],
            'condition_field' => ['required', Rule::in(['employment_status', 'grade', 'department', 'branch', 'attendance_days', 'join_date', 'resign_date'])],
            'operator' => ['required', Rule::in(['equals', 'not_equals', 'contains', 'gte', 'lte', 'between'])],
            'condition_value' => ['nullable', 'string', 'max:180'],
            'action' => ['required', Rule::in(['include', 'exclude', 'override_amount', 'require_review'])],
            'priority' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $rule = PayrollComponentRule::create([
            ...$validated,
            'priority' => $validated['priority'] ?? 10,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('payroll_component_rule.created', "Rule payroll {$rule->rule_name} dibuat.", $rule, [], $rule->toArray(), $request);

        return redirect()->route('payroll.components')->with('success', 'Rule komponen payroll berhasil disimpan.');
    }

    private function authorizePayroll(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage payroll settings') || $user?->hasPermission('manage modules'), 403);
    }

    private function splitList(?string $value): array
    {
        return $value ? collect(explode(',', $value))->map(fn ($item) => trim($item))->filter()->values()->all() : [];
    }
}
