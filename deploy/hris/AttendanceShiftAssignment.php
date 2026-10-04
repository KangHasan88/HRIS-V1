<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceShiftAssignment extends Model
{
    protected $fillable = [
        'attendance_shift_pattern_id',
        'employee_profile_id',
        'company_id',
        'branch_id',
        'assignment_scope',
        'rotation_cycle',
        'effective_start',
        'effective_end',
        'work_days',
        'off_days',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'effective_start' => 'date',
        'effective_end' => 'date',
        'work_days' => 'array',
        'off_days' => 'array',
    ];

    public function shiftPattern(): BelongsTo
    {
        return $this->belongsTo(AttendanceShiftPattern::class, 'attendance_shift_pattern_id');
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
