<?php

namespace App\Http\Controllers;

use App\Models\EmployeeBankAccount;
use App\Models\EmployeeProfile;
use App\Models\EssProfileUpdateRequest;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EssProfileUpdateController extends Controller
{
    public function ess(Request $request): View
    {
        $employee = $this->currentEmployee();
        $requests = collect();
        $primaryBank = null;

        if ($employee) {
            $employee->load(['bankAccounts']);
            $requests = EssProfileUpdateRequest::where('employee_profile_id', $employee->id)
                ->latest()
                ->limit(50)
                ->get();
            $primaryBank = $employee->bankAccounts->firstWhere('is_primary', true) ?? $employee->bankAccounts->first();
        }

        return view('ess.profile-updates', [
            'active' => 'ess-profile-updates',
            'employee' => $employee,
            'primaryBank' => $primaryBank,
            'requests' => $requests,
            'summary' => [
                'total' => $requests->count(),
                'submitted' => $requests->where('approval_status', 'submitted')->count(),
                'approved' => $requests->where('approval_status', 'approved')->count(),
                'rejected' => $requests->where('approval_status', 'rejected')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $this->currentEmployee();
        abort_unless($employee, 404, 'Employee profile belum terhubung dengan user login.');

        $validated = $request->validate([
            'request_number' => ['required', 'string', 'max:80', 'unique:ess_profile_update_requests,request_number'],
            'request_source' => ['required', Rule::in(['ess_webview', 'mobile_webview'])],
            'personal_email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:500'],
            'bank_name' => ['nullable', 'string', 'max:80'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'account_holder_name' => ['nullable', 'string', 'max:160'],
            'evidence_reference' => ['nullable', 'string', 'max:255'],
            'employee_note' => ['nullable', 'string', 'max:1200'],
        ]);

        $employee->load(['bankAccounts']);
        $primaryBank = $employee->bankAccounts->firstWhere('is_primary', true) ?? $employee->bankAccounts->first();
        $currentSnapshot = $this->snapshot($employee, $primaryBank);
        $requestedSnapshot = [
            'profile' => Arr::only($validated, [
                'personal_email',
                'phone',
                'emergency_contact_name',
                'emergency_contact_phone',
                'address',
            ]),
            'bank' => Arr::only($validated, [
                'bank_name',
                'account_number',
                'account_holder_name',
            ]),
        ];

        $profileUpdate = EssProfileUpdateRequest::create([
            'employee_profile_id' => $employee->id,
            'request_number' => $validated['request_number'],
            'request_source' => $validated['request_source'],
            'current_snapshot' => $currentSnapshot,
            'requested_snapshot' => $requestedSnapshot,
            'evidence_reference' => $validated['evidence_reference'] ?? null,
            'employee_note' => $validated['employee_note'] ?? null,
            'approval_status' => 'submitted',
            'submitted_at' => now(),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'ess_profile_update.submitted',
            "Update profil {$profileUpdate->request_number} diajukan oleh {$employee->employee_number}.",
            $profileUpdate,
            [],
            $profileUpdate->toArray(),
            $request,
        );

        return redirect()->route('ess.profile-updates')->with('success', 'Pengajuan update profil berhasil dikirim ke HR.');
    }

    public function admin(): View
    {
        $this->authorizeHrEditor();

        $requests = EssProfileUpdateRequest::with(['employeeProfile.company', 'employeeProfile.branch', 'reviewer'])
            ->latest()
            ->limit(100)
            ->get();

        return view('employee-profile-updates', [
            'active' => 'employee-profile-updates',
            'requests' => $requests,
            'summary' => [
                'total' => $requests->count(),
                'submitted' => $requests->where('approval_status', 'submitted')->count(),
                'approved' => $requests->where('approval_status', 'approved')->count(),
                'rejected' => $requests->where('approval_status', 'rejected')->count(),
            ],
        ]);
    }

    public function review(Request $request, EssProfileUpdateRequest $profileUpdateRequest): RedirectResponse
    {
        $this->authorizeHrEditor();

        $validated = $request->validate([
            'approval_status' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => ['nullable', 'string', 'max:1200'],
        ]);

        if ($profileUpdateRequest->approval_status !== 'submitted') {
            return redirect()->route('hris.employee-profile-updates')->with('success', 'Request ini sudah direview sebelumnya.');
        }

        $oldValues = $profileUpdateRequest->toArray();
        $profileUpdateRequest->update([
            'approval_status' => $validated['approval_status'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'] ?? null,
        ]);

        if ($validated['approval_status'] === 'approved') {
            $this->applyApprovedChanges($profileUpdateRequest);
        }

        AuditLogger::record(
            'ess_profile_update.reviewed',
            "Update profil {$profileUpdateRequest->request_number} {$validated['approval_status']}.",
            $profileUpdateRequest,
            $oldValues,
            $profileUpdateRequest->fresh()->toArray(),
            $request,
        );

        return redirect()->route('hris.employee-profile-updates')->with('success', 'Review update profil berhasil disimpan.');
    }

    private function currentEmployee(): ?EmployeeProfile
    {
        return EmployeeProfile::where('user_id', auth()->id())
            ->orWhere('work_email', auth()->user()?->email)
            ->first();
    }

    private function authorizeHrEditor(): void
    {
        $user = auth()->user();

        abort_unless($user?->hasRole('Super Admin') || $user?->hasPermission('edit employees'), 403);
    }

    private function snapshot(EmployeeProfile $employee, ?EmployeeBankAccount $bank): array
    {
        return [
            'profile' => [
                'personal_email' => $employee->personal_email,
                'phone' => $employee->phone,
                'emergency_contact_name' => $employee->emergency_contact_name,
                'emergency_contact_phone' => $employee->emergency_contact_phone,
                'address' => $employee->address_snapshot['address'] ?? null,
            ],
            'bank' => [
                'bank_name' => $bank?->bank_name,
                'account_number' => $bank?->account_number,
                'account_holder_name' => $bank?->account_holder_name,
            ],
        ];
    }

    private function applyApprovedChanges(EssProfileUpdateRequest $request): void
    {
        $employee = $request->employeeProfile()->firstOrFail();
        $payload = $request->requested_snapshot ?? [];
        $profile = $payload['profile'] ?? [];
        $bank = $payload['bank'] ?? [];

        $employee->fill([
            'personal_email' => $profile['personal_email'] ?: $employee->personal_email,
            'phone' => $profile['phone'] ?: $employee->phone,
            'emergency_contact_name' => $profile['emergency_contact_name'] ?: $employee->emergency_contact_name,
            'emergency_contact_phone' => $profile['emergency_contact_phone'] ?: $employee->emergency_contact_phone,
            'address_snapshot' => array_merge($employee->address_snapshot ?? [], [
                'address' => $profile['address'] ?: ($employee->address_snapshot['address'] ?? null),
            ]),
        ])->save();

        if (filled($bank['bank_name'] ?? null) || filled($bank['account_number'] ?? null) || filled($bank['account_holder_name'] ?? null)) {
            $primaryBank = $employee->bankAccounts()->where('is_primary', true)->first() ?? $employee->bankAccounts()->first();
            if ($primaryBank) {
                $primaryBank->update([
                    'bank_name' => $bank['bank_name'] ?: $primaryBank->bank_name,
                    'account_number' => $bank['account_number'] ?: $primaryBank->account_number,
                    'account_holder_name' => $bank['account_holder_name'] ?: $primaryBank->account_holder_name,
                    'is_primary' => true,
                ]);
            } else {
                $employee->bankAccounts()->create([
                    'bank_name' => $bank['bank_name'] ?: 'Bank belum ditentukan',
                    'account_number' => $bank['account_number'] ?: '-',
                    'account_holder_name' => $bank['account_holder_name'] ?: $employee->full_name,
                    'is_primary' => true,
                ]);
            }
        }
    }
}
