<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\ManagerClaimRequest;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EssReimbursementController extends Controller
{
    public function index(): View
    {
        $employee = $this->currentEmployee();
        $claims = collect();

        if ($employee) {
            $claims = ManagerClaimRequest::where('employee_profile_id', $employee->id)
                ->where('request_source', 'ess_webview')
                ->latest('claim_date')
                ->limit(60)
                ->get();
        }

        return view('ess.reimbursements', [
            'active' => 'ess-reimbursements',
            'employee' => $employee,
            'claims' => $claims,
            'summary' => [
                'total' => $claims->count(),
                'submitted' => $claims->where('approval_status', 'submitted')->count(),
                'approved' => $claims->where('approval_status', 'approved')->count(),
                'amount' => (float) $claims->whereIn('approval_status', ['submitted', 'approved'])->sum('amount'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $this->currentEmployee();
        abort_unless($employee, 404, 'Employee profile belum terhubung dengan user login.');

        $validated = $request->validate([
            'claim_number' => ['required', 'string', 'max:80', 'unique:manager_claim_requests,claim_number'],
            'claim_date' => ['required', 'date'],
            'claim_type' => ['required', Rule::in(['transport', 'meal', 'medical', 'training', 'other'])],
            'merchant_name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999999'],
            'attachment_reference' => ['required', 'string', 'max:255'],
            'receipt_capture_method' => ['required', Rule::in(['camera', 'gallery', 'file_upload', 'manual_reference'])],
            'receipt_captured_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1200'],
        ]);

        $claim = ManagerClaimRequest::create([
            ...$validated,
            'employee_profile_id' => $employee->id,
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'request_source' => 'ess_webview',
            'approval_status' => 'submitted',
            'current_approver' => 'Atasan langsung',
            'submitted_at' => now(),
            'receipt_captured_at' => $validated['receipt_captured_at'] ?? now(),
            'receipt_metadata' => [
                'source' => $validated['receipt_capture_method'],
                'requires_original_check' => $validated['receipt_capture_method'] !== 'camera',
                'submitted_from' => 'ESS WebView',
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'ess_reimbursement.submitted',
            "Reimbursement {$claim->claim_number} diajukan oleh {$employee->employee_number}.",
            $claim,
            [],
            $claim->toArray(),
            $request,
        );

        return redirect()->route('ess.reimbursements')->with('success', 'Pengajuan reimbursement berhasil dikirim.');
    }

    private function currentEmployee(): ?EmployeeProfile
    {
        return EmployeeProfile::where('user_id', auth()->id())
            ->orWhere('work_email', auth()->user()?->email)
            ->first();
    }
}
