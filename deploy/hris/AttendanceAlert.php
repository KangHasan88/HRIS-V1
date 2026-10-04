<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceAlert extends Model
{
    protected $fillable = [
        'alert_number',
        'employee_profile_id',
        'attendance_clock_event_id',
        'attendance_shift_assignment_id',
        'alert_date',
        'alert_type',
        'severity',
        'status',
        'late_minutes',
        'worked_hours',
        'fatigue_score',
        'compliance_rule',
        'owner_name',
        'due_date',
        'resolved_at',
        'resolved_by',
        'alert_snapshot',
        'trigger_reason',
        'follow_up_notes',
        'created_by',
    ];

    protected $casts = [
        'alert_date' => 'date',
        'due_date' => 'date',
        'resolved_at' => 'datetime',
        'worked_hours' => 'decimal:2',
        'fatigue_score' => 'decimal:2',
        'alert_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function clockEvent(): BelongsTo
    {
        return $this->belongsTo(AttendanceClockEvent::class, 'attendance_clock_event_id');
    }

    public function shiftAssignment(): BelongsTo
    {
        return $this->belongsTo(AttendanceShiftAssignment::class, 'attendance_shift_assignment_id');
    }
}
