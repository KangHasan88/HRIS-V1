<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrection;
use App\Models\BusinessTripRequest;
use App\Models\EmployeeProfile;
use App\Models\EssProfileUpdateRequest;
use App\Models\LeaveRequest;
use App\Models\ManagerBudgetAllocation;
use App\Models\ManagerClaimRequest;
use App\Models\ManagerHeadcountPlan;
use App\Models\ManagerOneOnOneLog;
use App\Models\ManagerPerformanceNote;
use App\Models\ManagerReplacementRequisition;
use App\Models\ManagerTeamDocumentRequest;
use App\Models\ManagerWorkCertificateApproval;
use App\Models\OvertimeRequest;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ManagerSelfServiceController extends Controller
{
    private const PENDING_STATUSES = ['submitted', 'pending', 'review', 'waiting_approval'];

    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance'), 403);

        $managerEmployee = $this->managerEmployee();
        $team = $this->team($managerEmployee);
        $teamIds = $team->pluck('id');
        $approvals = $this->approvalQueue($teamIds);
        $budgetAllocations = ManagerBudgetAllocation::where('manager_user_id', $user->id)->latest()->limit(20)->get();
        $performanceNotes = ManagerPerformanceNote::where('manager_user_id', $user->id)->latest('note_date')->limit(20)->get();
        $oneOnOnes = ManagerOneOnOneLog::where('manager_user_id', $user->id)->latest('meeting_date')->limit(20)->get();
        $headcountPlans = ManagerHeadcountPlan::where('manager_user_id', $user->id)->latest()->limit(20)->get();
        $replacementRequests = ManagerReplacementRequisition::where('manager_user_id', $user->id)->latest()->limit(20)->get();
        $teamDocuments = ManagerTeamDocumentRequest::where('manager_user_id', $user->id)->latest()->limit(20)->get();
        $certificates = ManagerWorkCertificateApproval::where('manager_user_id', $user->id)->latest()->limit(20)->get();

        return view('manager.self-service', [
            'active' => 'manager-self-service',
            'managerEmployee' => $managerEmployee,
            'team' => $team,
            'approvals' => $approvals,
            'budgetAllocations' => $budgetAllocations,
            'performanceNotes' => $performanceNotes,
            'oneOnOnes' => $oneOnOnes,
            'headcountPlans' => $headcountPlans,
            'replacementRequests' => $replacementRequests,
            'teamDocuments' => $teamDocuments,
            'certificates' => $certificates,
            'summary' => [
                'team_total' => $team->count(),
                'approval_pending' => $approvals->count(),
                'high_risk' => $approvals->where('risk', 'high')->count(),
                'budget_active' => $budgetAllocations->where('status', 'active')->count(),
                'performance_items' => $performanceNotes->count() + $oneOnOnes->count(),
                'headcount_items' => $headcountPlans->count() + $replacementRequests->count(),
                'document_items' => $teamDocuments->count() + $certificates->count(),
            ],
        ]);
    }

    private function managerEmployee(): ?EmployeeProfile
    {
        $user = auth()->user();

        return EmployeeProfile::with(['company', 'branch'])
            ->where('user_id', $user?->id)
            ->orWhere('work_email', $user?->email)
            ->orWhere('full_name', $user?->name)
            ->first();
    }

    private function team(?EmployeeProfile $managerEmployee): Collection
    {
        $user = auth()->user();
        $query = EmployeeProfile::with(['company', 'branch'])
            ->where('is_active', true)
            ->orderBy('full_name');

        if (! $user?->hasRole('Super Admin')) {
            if ($managerEmployee?->branch_id) {
                $query->where('branch_id', $managerEmployee->branch_id);
            } elseif ($user?->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
        }

        return $query->limit(120)->get();
    }

    private function approvalQueue(Collection $teamIds): Collection
    {
        return collect()
            ->merge($this->mapApproval('Cuti', LeaveRequest::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(50)->get(), 'request_number', route('manager.approval-inbox')))
            ->merge($this->mapApproval('Lembur', OvertimeRequest::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(50)->get(), 'request_number', route('manager.approval-inbox')))
            ->merge($this->mapApproval('Dinas', BusinessTripRequest::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(50)->get(), 'request_number', route('manager.approval-inbox')))
            ->merge($this->mapApproval('Koreksi Absensi', AttendanceCorrection::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(50)->get(), 'correction_number', route('manager.approval-inbox')))
            ->merge($this->mapApproval('Reimbursement', ManagerClaimRequest::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(50)->get(), 'claim_number', route('manager.approval-inbox')))
            ->merge($this->mapApproval('Update Profil', EssProfileUpdateRequest::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(50)->get(), 'request_number', route('manager.approval-inbox')))
            ->sortByDesc('submitted_at')
            ->values();
    }

    private function mapApproval(string $type, Collection $rows, string $numberField, string $route): Collection
    {
        return $rows->map(fn ($row) => [
            'type' => $type,
            'number' => $row->{$numberField} ?? '-',
            'employee' => $row->employeeProfile?->full_name ?: '-',
            'status' => $row->approval_status,
            'submitted_at' => $row->submitted_at ?? $row->created_at,
            'risk' => in_array($type, ['Koreksi Absensi', 'Reimbursement'], true) ? 'high' : 'normal',
            'route' => $route,
        ]);
    }
}
