<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    protected $fillable = [
        'correction_number',
        'employee_profile_id',
        'attendance_clock_event_id',
        'attendance_date',
        'correction_type',
        'original_event_at',
        'requested_event_at',
        'requested_event_type',
        'request_source',
        'approval_status',
        'current_approver',
        'submitted_at',
        'decided_at',
        'decided_by',
        'creates_clock_event',
        'generated_event_number',
        'attachment_reference',
        'correction_snapshot',
        'reason',
        'decision_notes',
        'created_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'original_event_at' => 'datetime',
        'requested_event_at' => 'datetime',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'creates_clock_event' => 'boolean',
        'correction_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function clockEvent(): BelongsTo
    {
        return $this->belongsTo(AttendanceClockEvent::class, 'attendance_clock_event_id');
    }
}
