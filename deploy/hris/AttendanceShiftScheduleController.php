<?php

namespace App\Http\Controllers;

use App\Models\AttendanceShiftAssignment;
use App\Models\AttendanceShiftPattern;
use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceShiftScheduleController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view attendance');

        $patterns = AttendanceShiftPattern::with(['company', 'branch'])
            ->latest()
            ->limit(120)
            ->get();

        $assignments = AttendanceShiftAssignment::with(['shiftPattern', 'employeeProfile', 'company', 'branch'])
            ->latest()
            ->limit(150)
            ->get();

        return view('attendance.shift-schedules', [
            'active' => 'attendance-shifts',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('code')->get(),
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'patterns' => $patterns,
            'assignments' => $assignments,
            'summary' => [
                'patterns' => $patterns->count(),
                'active_patterns' => $patterns->where('is_active', true)->count(),
                'assignments' => $assignments->count(),
                'published' => $assignments->where('status', 'published')->count(),
            ],
            'shiftTypes' => [
                'regular' => 'Reguler',
                'rotation' => 'Rotasi',
                'split' => 'Split shift',
                'long_shift' => 'Long shift',
                'night' => 'Shift malam',
                'off' => 'Off day',
            ],
            'rotationCycles' => [
                'fixed' => 'Tetap',
                'weekly' => 'Rotasi mingguan',
                'two_weeks' => 'Rotasi 2 mingguan',
                'monthly' => 'Rotasi bulanan',
                'custom' => 'Custom pattern',
            ],
            'scopeOptions' => [
                'employee' => 'Per karyawan',
                'company' => 'Perusahaan',
                'branch' => 'Cabang / Site',
            ],
            'dayOptions' => [
                'mon' => 'Senin',
                'tue' => 'Selasa',
                'wed' => 'Rabu',
                'thu' => 'Kamis',
                'fri' => 'Jumat',
                'sat' => 'Sabtu',
                'sun' => 'Minggu',
            ],
        ]);
    }

    public function storePattern(Request $request): RedirectResponse
    {
        Gate::authorize('manage shift schedules');

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['required', 'string', 'max:80', 'unique:attendance_shift_patterns,code'],
            'name' => ['required', 'string', 'max:180'],
            'shift_type' => ['required', Rule::in(['regular', 'rotation', 'split', 'long_shift', 'night', 'off'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'break_minutes' => ['required', 'integer', 'min:0', 'max:480'],
            'is_cross_day' => ['nullable', 'boolean'],
            'is_split_shift' => ['nullable', 'boolean'],
            'split_start_time' => ['nullable', 'date_format:H:i'],
            'split_end_time' => ['nullable', 'date_format:H:i'],
            'planned_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'grace_late_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'grace_early_leave_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $validated['is_cross_day'] = $request->boolean('is_cross_day');
        $validated['is_split_shift'] = $request->boolean('is_split_shift') || $validated['shift_type'] === 'split';
        $validated['is_active'] = true;
        $validated['created_by'] = auth()->id();

        $pattern = AttendanceShiftPattern::create($validated);

        AuditLogger::record(
            'attendance_shift_pattern.created',
            "Pola shift {$pattern->code} - {$pattern->name} dibuat.",
            $pattern,
            [],
            $pattern->toArray(),
            $request,
        );

        return redirect()->route('attendance.shift-schedules')->with('success', 'Pola shift berhasil dibuat.');
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        Gate::authorize('manage shift schedules');

        $validated = $request->validate([
            'attendance_shift_pattern_id' => ['required', 'exists:attendance_shift_patterns,id'],
            'employee_profile_id' => ['nullable', 'exists:employee_profiles,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'assignment_scope' => ['required', Rule::in(['employee', 'company', 'branch'])],
            'rotation_cycle' => ['required', Rule::in(['fixed', 'weekly', 'two_weeks', 'monthly', 'custom'])],
            'effective_start' => ['required', 'date'],
            'effective_end' => ['nullable', 'date', 'after_or_equal:effective_start'],
            'work_days' => ['nullable', 'array'],
            'work_days.*' => ['string', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'off_days' => ['nullable', 'array'],
            'off_days.*' => ['string', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        if ($validated['assignment_scope'] === 'employee') {
            $validated['company_id'] = null;
            $validated['branch_id'] = null;
        }

        if ($validated['assignment_scope'] === 'company') {
            $validated['employee_profile_id'] = null;
            $validated['branch_id'] = null;
        }

        if ($validated['assignment_scope'] === 'branch') {
            $validated['employee_profile_id'] = null;
        }

        $validated['created_by'] = auth()->id();

        $assignment = AttendanceShiftAssignment::create($validated);

        AuditLogger::record(
            'attendance_shift_assignment.created',
            "Roster shift {$assignment->shiftPattern?->code} dipasang untuk scope {$assignment->assignment_scope}.",
            $assignment,
            [],
            $assignment->toArray(),
            $request,
        );

        return redirect()->route('attendance.shift-schedules')->with('success', 'Roster shift berhasil dipasang.');
    }
}
