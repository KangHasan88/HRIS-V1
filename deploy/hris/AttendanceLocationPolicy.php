<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLocationPolicy extends Model
{
    protected $fillable = [
        'attendance_geofence_id',
        'employee_profile_id',
        'company_id',
        'branch_id',
        'policy_number',
        'policy_type',
        'assignment_scope',
        'effective_start',
        'effective_end',
        'approval_status',
        'approved_by',
        'approved_at',
        'allowed_days',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'effective_start' => 'date',
        'effective_end' => 'date',
        'approved_at' => 'datetime',
        'allowed_days' => 'array',
    ];

    public function geofence(): BelongsTo
    {
        return $this->belongsTo(AttendanceGeofence::class, 'attendance_geofence_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
