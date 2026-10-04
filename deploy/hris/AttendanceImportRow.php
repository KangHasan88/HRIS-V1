<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceImportRow extends Model
{
    protected $fillable = [
        'attendance_import_batch_id',
        'employee_profile_id',
        'employee_number',
        'employee_name',
        'raw_identifier',
        'event_type',
        'event_at',
        'source_device',
        'match_status',
        'anomaly_flags',
        'normalized_payload',
        'notes',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'anomaly_flags' => 'array',
        'normalized_payload' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AttendanceImportBatch::class, 'attendance_import_batch_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
