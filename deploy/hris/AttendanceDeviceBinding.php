<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceDeviceBinding extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'device_uid',
        'device_name',
        'platform',
        'app_version',
        'bound_at',
        'last_seen_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'bound_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function clockEvents(): HasMany
    {
        return $this->hasMany(AttendanceClockEvent::class);
    }
}
