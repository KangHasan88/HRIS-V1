<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceAnomalyDetection extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'attendance_clock_event_id',
        'detection_number',
        'detection_key',
        'anomaly_type',
        'severity',
        'detected_at',
        'period_start',
        'period_end',
        'score',
        'status',
        'evidence_snapshot',
        'recommendation',
        'reviewed_at',
        'reviewed_by',
        'created_by',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
        'score' => 'decimal:2',
        'evidence_snapshot' => 'array',
        'reviewed_at' => 'datetime',
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
