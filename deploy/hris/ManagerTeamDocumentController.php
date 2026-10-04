<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\ManagerTeamDocumentRequest;
use App\Models\ManagerWorkCertificateApproval;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagerTeamDocumentController extends Controller
{
    public function index(): View
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $team = $this->teamFor($manager);
        $teamIds = $team->pluck('id');

        $documentRequests = ManagerTeamDocumentRequest::with('employeeProfile')
            ->where('manager_user_id', $manager->id)
            ->whereIn('employee_profile_id', $teamIds)
            ->latest('needed_date')
            ->limit(80)
            ->get();

        $certificates = ManagerWorkCertificateApproval::with('employeeProfile')
            ->where('manager_user_id', $manager->id)
            ->whereIn('employee_profile_id', $teamIds)
            ->latest('effective_date')
            ->limit(80)
            ->get();

        return view('manager.team-documents', [
            'active' => 'manager-team-documents',
            'team' => $team,
            'documentRequests' => $documentRequests,
            'certificates' => $certificates,
            'summary' => [
                'team_total' => $team->count(),
                'document_open' => $documentRequests->whereIn('approval_status', ['draft', 'submitted', 'review'])->count(),
                'certificate_pending' => $certificates->where('manager_decision', 'pending')->count(),
                'approved' => $documentRequests->where('approval_status', 'approved')->count() + $certificates->where('manager_decision', 'approved')->count(),
            ],
        ]);
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $teamIds = $this->teamFor($manager)->pluck('id');
        $validated = $request->validate([
            'request_number' => ['required', 'string', 'max:80', 'unique:manager_team_document_requests,request_number'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'document_type' => ['required', Rule::in(['contract_copy', 'id_badge', 'assignment_letter', 'training_certificate', 'employment_document', 'other'])],
            'purpose' => ['required', 'string', 'max:180'],
            'needed_date' => ['nullable', 'date'],
            'delivery_channel' => ['required', Rule::in(['portal', 'email', 'printed', 'whatsapp'])],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'review', 'approved', 'rejected', 'cancelled'])],
            'current_approver' => ['nullable', 'string', 'max:160'],
            'request_note' => ['nullable', 'string', 'max:2400'],
            'hr_note' => ['nullable', 'string', 'max:2400'],
        ]);

        abort_unless($teamIds->contains((int) $validated['employee_profile_id']), 403);

        $document = ManagerTeamDocumentRequest::create([
            ...$validated,
            'manager_user_id' => $manager->id,
            'created_by' => $manager->id,
        ]);

        AuditLogger::record(
            'manager_team_document_request.created',
            "Pengajuan dokumen {$document->request_number} dibuat.",
            $document,
            [],
            $document->toArray(),
            $request,
        );

        return redirect()->route('manager.team-documents')->with('success', 'Pengajuan dokumen tim berhasil disimpan.');
    }

    public function storeCertificate(Request $request): RedirectResponse
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $teamIds = $this->teamFor($manager)->pluck('id');
        $validated = $request->validate([
            'certificate_number' => ['required', 'string', 'max:80', 'unique:manager_work_certificate_approvals,certificate_number'],
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'certificate_type' => ['required', Rule::in(['employment_statement', 'salary_statement', 'visa_support', 'bank_support', 'assignment_statement'])],
            'recipient_name' => ['nullable', 'string', 'max:160'],
            'recipient_institution' => ['nullable', 'string', 'max:180'],
            'effective_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'approval_status' => ['required', Rule::in(['draft', 'submitted', 'review', 'approved', 'rejected', 'cancelled'])],
            'manager_decision' => ['required', Rule::in(['pending', 'approved', 'rejected', 'needs_revision'])],
            'certificate_purpose' => ['required', 'string', 'max:2400'],
            'decision_note' => ['nullable', 'string', 'max:2400'],
        ]);

        abort_unless($teamIds->contains((int) $validated['employee_profile_id']), 403);

        $certificate = ManagerWorkCertificateApproval::create([
            ...$validated,
            'manager_user_id' => $manager->id,
            'decided_at' => $validated['manager_decision'] === 'pending' ? null : now(),
            'created_by' => $manager->id,
        ]);

        AuditLogger::record(
            'manager_work_certificate_approval.created',
            "Approval surat kerja {$certificate->certificate_number} dibuat.",
            $certificate,
            [],
            $certificate->toArray(),
            $request,
        );

        return redirect()->route('manager.team-documents')->with('success', 'Approval surat keterangan kerja berhasil disimpan.');
    }

    private function teamFor($manager): Collection
    {
        $managerEmployee = EmployeeProfile::where('user_id', $manager?->id)
            ->orWhere('work_email', $manager?->email)
            ->first();

        $query = EmployeeProfile::with(['branch', 'company'])
            ->where('is_active', true)
            ->orderBy('full_name');

        if ($managerEmployee?->branch_id && ! $manager?->hasRole('Super Admin')) {
            $query->where('branch_id', $managerEmployee->branch_id);
        } elseif ($manager?->branch_id && ! $manager?->hasRole('Super Admin')) {
            $query->where('branch_id', $manager->branch_id);
        }

        return $query->limit(150)->get();
    }
}
