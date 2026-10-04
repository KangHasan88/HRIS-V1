<?php

namespace App\Http\Controllers;

use App\Models\AttendanceClockEvent;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceExceptionReviewController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance'), 403);

        $managerEmployee = EmployeeProfile::where('user_id', $user?->id)
            ->orWhere('work_email', $user?->email)
            ->first();

        $teamQuery = EmployeeProfile::query()->where('is_active', true);
        if ($managerEmployee?->branch_id && ! $user?->hasRole('Super Admin')) {
            $teamQuery->where('branch_id', $managerEmployee->branch_id);
        } elseif ($user?->branch_id && ! $user?->hasRole('Super Admin')) {
            $teamQuery->where('branch_id', $user->branch_id);
        }

        $teamIds = $teamQuery->pluck('id');
        $eventsQuery = AttendanceClockEvent::with(['employeeProfile.branch', 'deviceBinding'])
            ->whereIn('employee_profile_id', $teamIds)
            ->where(function ($query) {
                $query->where('status', 'need_review')
                    ->orWhereNotNull('risk_flags')
                    ->orWhereNull('latitude')
                    ->orWhereNull('longitude')
                    ->orWhereNull('selfie_reference');
            })
            ->latest('event_at');

        if ($request->filled('review_status')) {
            $eventsQuery->where('review_status', $request->query('review_status'));
        }

        $events = $eventsQuery->limit(120)->get();

        return view('attendance.exception-reviews', [
            'active' => 'manager-attendance-exceptions',
            'events' => $events,
            'filterStatus' => $request->query('review_status', ''),
            'summary' => [
                'total' => $events->count(),
                'pending' => $events->where('review_status', 'pending')->count(),
                'approved' => $events->where('review_status', 'approved')->count(),
                'rejected' => $events->where('review_status', 'rejected')->count(),
            ],
        ]);
    }

    public function update(Request $request, AttendanceClockEvent $clockEvent): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('approve attendance'), 403);

        $validated = $request->validate([
            'review_decision' => ['required', Rule::in(['approve_exception', 'reject_exception', 'needs_hr_follow_up'])],
            'review_notes' => ['required', 'string', 'max:1200'],
        ]);

        $before = $clockEvent->toArray();
        $reviewStatus = match ($validated['review_decision']) {
            'approve_exception' => 'approved',
            'reject_exception' => 'rejected',
            default => 'follow_up',
        };

        $eventStatus = match ($validated['review_decision']) {
            'approve_exception' => 'approved',
            'reject_exception' => 'rejected',
            default => 'need_review',
        };

        $clockEvent->forceFill([
            'status' => $eventStatus,
            'review_status' => $reviewStatus,
            'review_decision' => $validated['review_decision'],
            'reviewed_at' => now(),
            'reviewed_by' => $user?->name ?? 'System',
            'review_notes' => $validated['review_notes'],
        ])->save();

        AuditLogger::record(
            'attendance_exception.reviewed',
            "Exception absensi {$clockEvent->event_number} direview oleh {$clockEvent->reviewed_by}.",
            $clockEvent,
            $before,
            $clockEvent->fresh()->toArray(),
            $request,
        );

        return redirect()->route('manager.attendance-exceptions')->with('success', 'Review pengecualian absensi berhasil disimpan.');
    }
}
