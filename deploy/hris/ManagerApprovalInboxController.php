<?php

namespace App\Http\Controllers;

use App\Models\ApprovalDelegation;
use App\Models\AttendanceCorrection;
use App\Models\BusinessTripRequest;
use App\Models\EmployeeProfile;
use App\Models\EssProfileUpdateRequest;
use App\Models\LeaveRequest;
use App\Models\ManagerClaimRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagerApprovalInboxController extends Controller
{
    private const PENDING_STATUSES = ['submitted', 'pending', 'review', 'waiting_approval'];

    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance'), 403);

        $teamIds = $this->teamIds($user);
        $items = $this->approvalItems($teamIds);
        $delegations = ApprovalDelegation::with(['delegator', 'delegate'])
            ->where(function ($query) use ($user) {
                $query->where('delegator_user_id', $user->id)
                    ->orWhere('delegate_user_id', $user->id);
            })
            ->latest()
            ->limit(30)
            ->get();

        return view('manager.approval-inbox', [
            'active' => 'manager-approval-inbox',
            'items' => $items,
            'delegations' => $delegations,
            'delegates' => User::with('role')->where('id', '!=', $user->id)->orderBy('name')->limit(80)->get(),
            'summary' => [
                'pending' => $items->count(),
                'high_risk' => $items->where('risk_level', 'high')->count(),
                'medium_risk' => $items->where('risk_level', 'medium')->count(),
                'delegations' => $delegations->where('status', 'active')->count(),
            ],
        ]);
    }

    public function bulkDecision(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance'), 403);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*' => ['required', 'string', 'max:80'],
            'decision_notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $teamIds = $this->teamIds($user);
        $processed = 0;

        foreach ($validated['items'] as $token) {
            [$type, $id] = array_pad(explode(':', $token, 2), 2, null);
            $model = $this->findModel($type, (int) $id);

            if (! $model || ! $this->modelBelongsToTeam($model, $teamIds)) {
                continue;
            }

            $before = $model->toArray();
            $this->applyDecision($model, $validated['decision'], $validated['decision_notes'] ?? null, $user);
            $processed++;

            AuditLogger::record(
                'manager_approval.bulk_decision',
                "Manager {$validated['decision']} {$type}:{$id}.",
                $model,
                $before,
                $model->fresh()?->toArray() ?? [],
                $request,
            );
        }

        return redirect()->route('manager.approval-inbox')->with('success', "{$processed} approval berhasil diproses.");
    }

    public function storeDelegation(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance'), 403);

        $validated = $request->validate([
            'delegate_user_id' => ['required', 'exists:users,id'],
            'scope_type' => ['required', Rule::in(['all', 'leave', 'overtime', 'business_trip', 'attendance_correction', 'reimbursement', 'profile_update'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1200'],
        ]);

        $delegation = ApprovalDelegation::create([
            ...$validated,
            'delegator_user_id' => $user->id,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        AuditLogger::record(
            'manager_approval.delegation_created',
            'Delegasi approval manager dibuat.',
            $delegation,
            [],
            $delegation->toArray(),
            $request,
        );

        return redirect()->route('manager.approval-inbox')->with('success', 'Delegasi approval berhasil dibuat.');
    }

    private function approvalItems(Collection $teamIds): Collection
    {
        return collect()
            ->merge($this->mapModels('leave', 'Cuti', LeaveRequest::with(['employeeProfile', 'policyType'])->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(80)->get()))
            ->merge($this->mapModels('overtime', 'Lembur', OvertimeRequest::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(80)->get()))
            ->merge($this->mapModels('business_trip', 'Dinas', BusinessTripRequest::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(80)->get()))
            ->merge($this->mapModels('attendance_correction', 'Koreksi Absensi', AttendanceCorrection::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(80)->get()))
            ->merge($this->mapModels('reimbursement', 'Reimbursement', ManagerClaimRequest::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(80)->get()))
            ->merge($this->mapModels('profile_update', 'Update Profil', EssProfileUpdateRequest::with('employeeProfile')->whereIn('employee_profile_id', $teamIds)->whereIn('approval_status', self::PENDING_STATUSES)->latest('submitted_at')->limit(80)->get()))
            ->sortByDesc('submitted_at')
            ->values();
    }

    private function mapModels(string $type, string $label, Collection $models): Collection
    {
        return $models->map(function (Model $model) use ($type, $label) {
            $submittedAt = $model->submitted_at ?? $model->created_at;
            $amount = $model->estimated_amount ?? $model->estimated_budget ?? $model->amount ?? null;
            $daysWaiting = $submittedAt ? now()->diffInDays($submittedAt) : 0;
            $risk = $this->risk($type, $model, $daysWaiting);

            return [
                'token' => $type . ':' . $model->id,
                'type' => $type,
                'label' => $label,
                'number' => $model->request_number ?? $model->correction_number ?? $model->claim_number,
                'employee' => $model->employeeProfile?->full_name ?? '-',
                'employee_number' => $model->employeeProfile?->employee_number ?? '-',
                'submitted_at' => $submittedAt,
                'date_range' => $this->dateRange($model),
                'amount' => $amount,
                'risk_level' => $risk['level'],
                'risk_reason' => $risk['reason'],
                'status' => $model->approval_status,
                'current_approver' => $model->current_approver ?? 'Atasan langsung',
            ];
        });
    }

    private function risk(string $type, Model $model, int $daysWaiting): array
    {
        $amount = (float) ($model->estimated_amount ?? $model->estimated_budget ?? $model->amount ?? 0);
        $duration = (float) ($model->requested_days ?? $model->requested_hours ?? 0);

        if ($daysWaiting >= 3 || $amount >= 5000000 || $duration >= 5) {
            return ['level' => 'high', 'reason' => 'Menunggu lama, nilai/durasi besar, atau berpotensi dampak payroll.'];
        }

        if ($daysWaiting >= 1 || $amount >= 1000000 || in_array($type, ['attendance_correction', 'profile_update'], true)) {
            return ['level' => 'medium', 'reason' => 'Perlu dicek karena ada dampak data karyawan, absensi, atau biaya.'];
        }

        return ['level' => 'low', 'reason' => 'Risiko rendah, data utama terlihat normal.'];
    }

    private function dateRange(Model $model): string
    {
        $start = $model->start_date ?? $model->overtime_date ?? $model->attendance_date ?? $model->claim_date ?? $model->submitted_at ?? null;
        $end = $model->end_date ?? null;

        if (! $start) {
            return '-';
        }

        return $end && $end->ne($start) ? $start->format('d M Y') . ' - ' . $end->format('d M Y') : $start->format('d M Y');
    }

    private function teamIds(User $user): Collection
    {
        $managerEmployee = EmployeeProfile::where('user_id', $user->id)
            ->orWhere('work_email', $user->email)
            ->first();

        $query = EmployeeProfile::where('is_active', true);

        if ($managerEmployee?->branch_id && ! $user->hasRole('Super Admin')) {
            $query->where('branch_id', $managerEmployee->branch_id);
        } elseif ($user->branch_id && ! $user->hasRole('Super Admin')) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query->pluck('id');
    }

    private function findModel(?string $type, int $id): ?Model
    {
        return match ($type) {
            'leave' => LeaveRequest::find($id),
            'overtime' => OvertimeRequest::find($id),
            'business_trip' => BusinessTripRequest::find($id),
            'attendance_correction' => AttendanceCorrection::find($id),
            'reimbursement' => ManagerClaimRequest::find($id),
            'profile_update' => EssProfileUpdateRequest::find($id),
            default => null,
        };
    }

    private function modelBelongsToTeam(Model $model, Collection $teamIds): bool
    {
        return $teamIds->contains((int) $model->employee_profile_id);
    }

    private function applyDecision(Model $model, string $decision, ?string $notes, User $user): void
    {
        if ($model instanceof EssProfileUpdateRequest) {
            $model->forceFill([
                'approval_status' => $decision,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'review_note' => $notes,
            ])->save();

            return;
        }

        $model->forceFill([
            'approval_status' => $decision,
            'decided_at' => now(),
            'decided_by' => $user->name,
            'decision_notes' => $notes,
        ])->save();
    }
}
