<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\LeavePolicyRule;
use App\Models\LeavePolicyType;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeavePolicyController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view leave');

        $types = LeavePolicyType::withCount('rules')->latest()->limit(80)->get();
        $rules = LeavePolicyRule::with(['policyType', 'company', 'branch'])->latest()->limit(120)->get();

        return view('leave.policies', [
            'active' => 'leave-policies',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::with('company')->orderBy('code')->get(),
            'types' => $types,
            'rules' => $rules,
            'summary' => [
                'types' => $types->count(),
                'active' => $types->where('status', 'active')->count(),
                'carry_forward' => $types->where('carry_forward_allowed', true)->count(),
                'rules' => $rules->count(),
            ],
            'categoryOptions' => [
                'annual' => 'Cuti tahunan',
                'sick' => 'Sakit',
                'special' => 'Cuti khusus',
                'unpaid' => 'Unpaid leave',
                'maternity' => 'Melahirkan',
                'other' => 'Lainnya',
            ],
            'employmentStatusOptions' => [
                'permanent' => 'Permanent',
                'contract' => 'Contract',
                'probation' => 'Probation',
                'daily' => 'Daily worker',
                'all' => 'Semua status',
            ],
        ]);
    }

    public function storeType(Request $request): RedirectResponse
    {
        Gate::authorize('approve leave');

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:80', 'unique:leave_policy_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['annual', 'sick', 'special', 'unpaid', 'maternity', 'other'])],
            'unit' => ['required', Rule::in(['day', 'half_day', 'hour'])],
            'annual_quota_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'max_days_per_request' => ['nullable', 'integer', 'min:1', 'max:365'],
            'requires_balance' => ['nullable', 'boolean'],
            'is_paid' => ['nullable', 'boolean'],
            'requires_attachment' => ['nullable', 'boolean'],
            'approval_required' => ['nullable', 'boolean'],
            'carry_forward_allowed' => ['nullable', 'boolean'],
            'carry_forward_max_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'carry_forward_expiry_months' => ['required', 'integer', 'min:0', 'max:24'],
            'negative_balance_allowed' => ['nullable', 'boolean'],
            'prorate_on_join' => ['nullable', 'boolean'],
            'gender_rule' => ['required', Rule::in(['all', 'male', 'female'])],
            'min_service_months' => ['required', 'integer', 'min:0', 'max:240'],
            'status' => ['required', Rule::in(['active', 'draft', 'inactive'])],
            'description' => ['nullable', 'string', 'max:1200'],
        ]);

        $type = LeavePolicyType::create([
            ...$validated,
            'requires_balance' => $request->boolean('requires_balance'),
            'is_paid' => $request->boolean('is_paid'),
            'requires_attachment' => $request->boolean('requires_attachment'),
            'approval_required' => $request->boolean('approval_required'),
            'carry_forward_allowed' => $request->boolean('carry_forward_allowed'),
            'negative_balance_allowed' => $request->boolean('negative_balance_allowed'),
            'prorate_on_join' => $request->boolean('prorate_on_join'),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'leave_policy_type.created',
            "Jenis kebijakan cuti {$type->code} dibuat.",
            $type,
            [],
            $type->toArray(),
            $request,
        );

        return redirect()->route('leave.policies')->with('success', 'Jenis kebijakan cuti berhasil dibuat.');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        Gate::authorize('approve leave');

        $validated = $request->validate([
            'leave_policy_type_id' => ['required', 'exists:leave_policy_types,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'employment_status' => ['nullable', Rule::in(['permanent', 'contract', 'probation', 'daily', 'all'])],
            'effective_start' => ['required', 'date'],
            'effective_end' => ['nullable', 'date', 'after_or_equal:effective_start'],
            'accrual_frequency' => ['required', Rule::in(['none', 'monthly', 'yearly', 'anniversary'])],
            'accrual_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'reset_month' => ['required', 'integer', 'min:1', 'max:12'],
            'include_holidays' => ['nullable', 'boolean'],
            'include_weekends' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'draft', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $rule = LeavePolicyRule::create([
            ...$validated,
            'employment_status' => $validated['employment_status'] === 'all' ? null : ($validated['employment_status'] ?? null),
            'include_holidays' => $request->boolean('include_holidays'),
            'include_weekends' => $request->boolean('include_weekends'),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'leave_policy_rule.created',
            "Aturan kebijakan cuti {$rule->policyType?->code} dibuat.",
            $rule,
            [],
            $rule->toArray(),
            $request,
        );

        return redirect()->route('leave.policies')->with('success', 'Aturan kebijakan cuti berhasil dibuat.');
    }
}
