<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\ManagerClaimRequest;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollReimbursementController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage payroll settings');

        $status = $request->string('status')->toString();
        $period = $request->string('period', now()->format('Y-m'))->toString();

        $claims = ManagerClaimRequest::with(['employeeProfile', 'company', 'branch', 'reviewer'])
            ->when($status !== '', fn ($query) => $query->where('approval_status', $status))
            ->when($period !== '', fn ($query) => $query->where(function ($nested) use ($period) {
                $nested->where('payroll_period', $period)
                    ->orWhere(function ($fallback) use ($period) {
                        $fallback->whereNull('payroll_period')
                            ->whereYear('claim_date', substr($period, 0, 4))
                            ->whereMonth('claim_date', substr($period, 5, 2));
                    });
            }))
            ->latest('claim_date')
            ->limit(120)
            ->get();

        return view('payroll.reimbursements', [
            'active' => 'payroll-reimbursements',
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('full_name')->limit(150)->get(),
            'claims' => $claims,
            'filters' => [
                'status' => $status,
                'period' => $period,
            ],
            'summary' => [
                'total' => $claims->count(),
                'submitted' => $claims->where('approval_status', 'submitted')->count(),
                'approved' => $claims->where('approval_status', 'approved')->count(),
                'posting' => $claims->where('payroll_posting_status', 'ready_for_payroll')->count(),
                'amount' => (float) $claims->whereIn('approval_status', ['submitted', 'approved'])->sum('amount'),
                'risk' => $claims->where('duplicate_risk_score', '>=', 60)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $this->validatedPayload($request, true);
        $employee = EmployeeProfile::findOrFail($data['employee_profile_id']);
        $claim = ManagerClaimRequest::create([
            ...$data,
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'request_source' => 'payroll_admin',
            'approval_status' => $data['approval_status'] ?? 'submitted',
            'current_approver' => $data['current_approver'] ?? 'Payroll Reviewer',
            'submitted_at' => now(),
            'created_by' => auth()->id(),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'receipt_metadata' => $this->receiptMetadata($data),
        ]);

        AuditLogger::record('payroll_reimbursement.created', "Klaim reimbursement {$claim->claim_number} dicatat payroll.", $claim, [], $claim->toArray(), $request);

        return redirect()->route('payroll.reimbursements')->with('success', 'Klaim reimbursement berhasil dicatat.');
    }

    public function review(Request $request, ManagerClaimRequest $claim): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $this->validatedPayload($request, false);
        $before = $claim->toArray();
        $claim->fill($data);
        $claim->reviewed_by = auth()->id();
        $claim->reviewed_at = now();
        $claim->decided_at = in_array($data['approval_status'], ['approved', 'rejected'], true) ? now() : $claim->decided_at;
        $claim->decided_by = in_array($data['approval_status'], ['approved', 'rejected'], true) ? auth()->user()?->name : $claim->decided_by;
        $claim->receipt_metadata = $this->receiptMetadata($data, $claim->receipt_metadata ?? []);
        $claim->save();

        AuditLogger::record('payroll_reimbursement.reviewed', "Klaim reimbursement {$claim->claim_number} direview payroll.", $claim, $before, $claim->toArray(), $request);

        return redirect()->route('payroll.reimbursements')->with('success', 'Review reimbursement berhasil diperbarui.');
    }

    private function validatedPayload(Request $request, bool $creating): array
    {
        $presence = $creating ? 'required' : 'sometimes';
        $claimNumberRules = [$presence, 'string', 'max:80'];
        if ($creating) {
            $claimNumberRules[] = 'unique:manager_claim_requests,claim_number';
        }

        return $request->validate([
            'employee_profile_id' => [$presence, 'exists:employee_profiles,id'],
            'claim_number' => $claimNumberRules,
            'claim_date' => [$presence, 'date'],
            'claim_type' => [$presence, Rule::in(['transport', 'meal', 'medical', 'training', 'other'])],
            'amount' => [$presence, 'numeric', 'min:1', 'max:999999999999'],
            'merchant_name' => ['nullable', 'string', 'max:255'],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
            'receipt_capture_method' => ['nullable', Rule::in(['camera', 'gallery', 'file_upload', 'manual_reference'])],
            'ocr_status' => ['required', Rule::in(['not_scanned', 'scanned', 'needs_review', 'verified', 'failed'])],
            'ocr_confidence' => ['required', 'integer', 'min:0', 'max:100'],
            'ocr_vendor_name' => ['nullable', 'string', 'max:255'],
            'ocr_receipt_date' => ['nullable', 'date'],
            'ocr_total_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'duplicate_risk_score' => ['required', 'integer', 'min:0', 'max:100'],
            'payroll_treatment' => ['required', Rule::in(['non_taxable_reimbursement', 'taxable_benefit', 'deduct_from_cash_advance', 'exclude_from_payroll'])],
            'payroll_period' => ['nullable', 'date_format:Y-m'],
            'payroll_posting_status' => ['required', Rule::in(['not_posted', 'ready_for_payroll', 'posted_to_payroll', 'excluded'])],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'cancelled'])],
            'current_approver' => ['nullable', 'string', 'max:255'],
            'decision_notes' => ['nullable', 'string', 'max:1200'],
            'description' => ['nullable', 'string', 'max:1200'],
        ]);
    }

    private function receiptMetadata(array $data, array $existing = []): array
    {
        $claimAmount = (float) ($data['amount'] ?? 0);
        $ocrAmount = isset($data['ocr_total_amount']) ? (float) $data['ocr_total_amount'] : null;

        return [
            ...$existing,
            'ocr_review' => [
                'amount_difference' => $ocrAmount === null ? null : $claimAmount - $ocrAmount,
                'needs_manual_check' => ($data['ocr_status'] ?? 'not_scanned') !== 'verified'
                    || (int) ($data['duplicate_risk_score'] ?? 0) >= 60
                    || ($ocrAmount !== null && abs($claimAmount - $ocrAmount) > 1000),
                'reviewed_from' => 'Payroll Reimbursement',
            ],
        ];
    }
}
