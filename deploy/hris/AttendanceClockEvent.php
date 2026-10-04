<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceClockEvent extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'attendance_shift_assignment_id',
        'attendance_device_binding_id',
        'event_number',
        'event_type',
        'event_at',
        'source',
        'latitude',
        'longitude',
        'gps_accuracy_meter',
        'location_label',
        'selfie_reference',
        'liveness_score',
        'is_liveness_passed',
        'is_device_trusted',
        'device_uid',
        'device_name',
        'status',
        'review_status',
        'review_decision',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'risk_flags',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_liveness_passed' => 'boolean',
        'is_device_trusted' => 'boolean',
        'reviewed_at' => 'datetime',
        'risk_flags' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function shiftAssignment(): BelongsTo
    {
        return $this->belongsTo(AttendanceShiftAssignment::class, 'attendance_shift_assignment_id');
    }

    public function deviceBinding(): BelongsTo
    {
        return $this->belongsTo(AttendanceDeviceBinding::class, 'attendance_device_binding_id');
    }
}
