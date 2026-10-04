<?php

namespace App\Http\Controllers;

use App\Models\AttendanceShiftAssignment;
use App\Models\BusinessTripRequest;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagerTeamCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $manager = auth()->user();
        abort_unless($manager?->hasPermission('view attendance'), 403);

        $start = $request->filled('start_date') ? Carbon::parse($request->date('start_date'))->startOfDay() : now()->startOfMonth();
        $end = $request->filled('end_date') ? Carbon::parse($request->date('end_date'))->startOfDay() : now()->endOfMonth();
        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $team = $this->teamFor($manager);
        $teamIds = $team->pluck('id');

        $events = collect()
            ->merge($this->leaveEvents($teamIds, $start, $end))
            ->merge($this->tripEvents($teamIds, $start, $end))
            ->merge($this->overtimeEvents($teamIds, $start, $end))
            ->merge($this->shiftEvents($teamIds, $start, $end))
            ->merge($this->cutoffEvents($start, $end))
            ->sortBy(['date', 'type', 'employee'])
            ->values();

        return view('manager.team-calendar', [
            'active' => 'manager-team-calendar',
            'team' => $team,
            'events' => $events,
            'filters' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'type' => $request->input('type', 'all'),
            ],
            'summary' => [
                'team_total' => $team->count(),
                'leave' => $events->where('type', 'leave')->count(),
                'shift' => $events->where('type', 'shift')->count(),
                'trip' => $events->where('type', 'trip')->count(),
                'cutoff' => $events->where('type', 'cutoff')->count(),
            ],
        ]);
    }

    private function teamFor($manager)
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

    private function leaveEvents($teamIds, Carbon $start, Carbon $end)
    {
        return LeaveRequest::with(['employeeProfile', 'policyType'])
            ->whereIn('employee_profile_id', $teamIds)
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->get()
            ->map(fn (LeaveRequest $leave) => [
                'date' => $leave->start_date,
                'end_date' => $leave->end_date,
                'type' => 'leave',
                'label' => $leave->policyType?->name ?: 'Cuti',
                'employee' => $leave->employeeProfile?->full_name,
                'meta' => $leave->requested_days . ' hari - ' . ucfirst($leave->approval_status),
                'status' => $leave->approval_status,
                'source' => $leave->request_number,
                'color' => 'bg-blue-50 text-blue-700',
            ]);
    }

    private function tripEvents($teamIds, Carbon $start, Carbon $end)
    {
        return BusinessTripRequest::with('employeeProfile')
            ->whereIn('employee_profile_id', $teamIds)
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->get()
            ->map(fn (BusinessTripRequest $trip) => [
                'date' => $trip->start_date,
                'end_date' => $trip->end_date,
                'type' => 'trip',
                'label' => 'Dinas ' . ($trip->destination_city ?: $trip->destination_site ?: 'luar kantor'),
                'employee' => $trip->employeeProfile?->full_name,
                'meta' => ucfirst($trip->approval_status) . ' - ' . ($trip->transport_mode ?: 'Transport belum diisi'),
                'status' => $trip->approval_status,
                'source' => $trip->request_number,
                'color' => 'bg-purple-50 text-purple-700',
            ]);
    }

    private function overtimeEvents($teamIds, Carbon $start, Carbon $end)
    {
        return OvertimeRequest::with('employeeProfile')
            ->whereIn('employee_profile_id', $teamIds)
            ->whereBetween('overtime_date', [$start, $end])
            ->get()
            ->map(fn (OvertimeRequest $overtime) => [
                'date' => $overtime->overtime_date,
                'end_date' => $overtime->overtime_date,
                'type' => 'overtime',
                'label' => 'Lembur ' . str_replace('_', ' ', $overtime->overtime_type),
                'employee' => $overtime->employeeProfile?->full_name,
                'meta' => $overtime->requested_hours . ' jam - ' . ucfirst($overtime->approval_status),
                'status' => $overtime->approval_status,
                'source' => $overtime->request_number,
                'color' => 'bg-amber-50 text-amber-700',
            ]);
    }

    private function shiftEvents($teamIds, Carbon $start, Carbon $end)
    {
        return AttendanceShiftAssignment::with(['employeeProfile', 'shiftPattern'])
            ->where(fn ($query) => $query->whereIn('employee_profile_id', $teamIds)->orWhereNull('employee_profile_id'))
            ->whereDate('effective_start', '<=', $end)
            ->where(fn ($query) => $query->whereNull('effective_end')->orWhereDate('effective_end', '>=', $start))
            ->where('status', 'active')
            ->limit(60)
            ->get()
            ->map(fn (AttendanceShiftAssignment $assignment) => [
                'date' => $assignment->effective_start->greaterThan($start) ? $assignment->effective_start : $start,
                'end_date' => $assignment->effective_end && $assignment->effective_end->lessThan($end) ? $assignment->effective_end : $end,
                'type' => 'shift',
                'label' => $assignment->shiftPattern?->name ?: 'Shift',
                'employee' => $assignment->employeeProfile?->full_name ?: ucfirst($assignment->assignment_scope),
                'meta' => ($assignment->shiftPattern?->start_time ?: '-') . ' - ' . ($assignment->shiftPattern?->end_time ?: '-'),
                'status' => $assignment->status,
                'source' => $assignment->rotation_cycle,
                'color' => 'bg-green-50 text-green-700',
            ]);
    }

    private function cutoffEvents(Carbon $start, Carbon $end)
    {
        $events = collect();
        foreach (CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth()) as $month) {
            $payrollCutoff = $month->copy()->day(min(25, $month->daysInMonth));
            $attendanceCutoff = $month->copy()->endOfMonth()->startOfDay();

            foreach ([['Payroll cutoff', $payrollCutoff], ['Attendance cutoff', $attendanceCutoff]] as [$label, $date]) {
                if ($date->betweenIncluded($start, $end)) {
                    $events->push([
                        'date' => $date,
                        'end_date' => $date,
                        'type' => 'cutoff',
                        'label' => $label,
                        'employee' => 'Semua tim',
                        'meta' => 'Marker operasional untuk reminder approval dan payroll',
                        'status' => 'system',
                        'source' => $date->format('F Y'),
                        'color' => 'bg-red-50 text-red-700',
                    ]);
                }
            }
        }

        return $events;
    }
}
