<?php

namespace App\Http\Controllers;

use App\Models\AttendanceClockEvent;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\View\View;

class ManagerDashboardController extends Controller
{
    public function index(): View
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $managerEmployee = EmployeeProfile::where('user_id', $manager?->id)
            ->orWhere('work_email', $manager?->email)
            ->first();

        $teamQuery = EmployeeProfile::with(['branch', 'company'])
            ->where('is_active', true)
            ->orderBy('full_name');

        if ($managerEmployee?->branch_id && ! $manager?->hasRole('Super Admin')) {
            $teamQuery->where('branch_id', $managerEmployee->branch_id);
        } elseif ($manager?->branch_id && ! $manager?->hasRole('Super Admin')) {
            $teamQuery->where('branch_id', $manager->branch_id);
        }

        $team = $teamQuery->limit(120)->get();
        $teamIds = $team->pluck('id');
        $today = today();

        $clockEvents = AttendanceClockEvent::with('employeeProfile')
            ->whereIn('employee_profile_id', $teamIds)
            ->whereDate('event_at', $today)
            ->latest('event_at')
            ->get();

        $leaveRequests = LeaveRequest::with(['employeeProfile', 'policyType'])
            ->whereIn('employee_profile_id', $teamIds)
            ->whereIn('approval_status', ['submitted', 'pending', 'review'])
            ->latest('submitted_at')
            ->limit(30)
            ->get();

        $overtimeRequests = OvertimeRequest::with('employeeProfile')
            ->whereIn('employee_profile_id', $teamIds)
            ->whereIn('approval_status', ['submitted', 'pending', 'review'])
            ->latest('submitted_at')
            ->limit(30)
            ->get();

        $birthdays = $team
            ->filter(fn (EmployeeProfile $employee) => $employee->birth_date)
            ->map(function (EmployeeProfile $employee) use ($today) {
                $birthday = Carbon::parse($employee->birth_date)->year($today->year);
                if ($birthday->lt($today)) {
                    $birthday->addYear();
                }

                return [
                    'employee' => $employee,
                    'date' => $birthday,
                    'days' => $today->diffInDays($birthday),
                ];
            })
            ->sortBy('date')
            ->take(10)
            ->values();

        $clockedInIds = $clockEvents->where('event_type', 'clock_in')->pluck('employee_profile_id')->unique();
        $needReview = $clockEvents->where('status', 'need_review')->count();

        return view('manager.dashboard', [
            'active' => 'manager-dashboard',
            'managerEmployee' => $managerEmployee,
            'team' => $team,
            'clockEvents' => $clockEvents->take(20),
            'leaveRequests' => $leaveRequests,
            'overtimeRequests' => $overtimeRequests,
            'birthdays' => $birthdays,
            'summary' => [
                'team_total' => $team->count(),
                'present_today' => $clockedInIds->count(),
                'not_clocked_in' => max(0, $team->count() - $clockedInIds->count()),
                'need_review' => $needReview,
                'leave_pending' => $leaveRequests->count(),
                'overtime_pending' => $overtimeRequests->count(),
                'birthday_30_days' => $birthdays->where('days', '<=', 30)->count(),
            ],
        ]);
    }
}
