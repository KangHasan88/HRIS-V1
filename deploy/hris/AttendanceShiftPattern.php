<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceShiftPattern extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'shift_type',
        'start_time',
        'end_time',
        'break_minutes',
        'is_cross_day',
        'is_split_shift',
        'split_start_time',
        'split_end_time',
        'planned_hours',
        'grace_late_minutes',
        'grace_early_leave_minutes',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_cross_day' => 'boolean',
        'is_split_shift' => 'boolean',
        'planned_hours' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AttendanceShiftAssignment::class);
    }
}
