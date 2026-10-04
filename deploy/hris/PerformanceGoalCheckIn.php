<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceGoalCheckIn extends Model
{
    protected $fillable = [
        'performance_goal_id',
        'employee_profile_id',
        'reviewer_user_id',
        'checkin_number',
        'checkin_date',
        'previous_value',
        'new_value',
        'progress_percent',
        'confidence_level',
        'risk_status',
        'achievement_notes',
        'blocker_notes',
        'next_action',
        'status',
        'reviewed_at',
        'created_by',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'previous_value' => 'decimal:2',
        'new_value' => 'decimal:2',
        'progress_percent' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(PerformanceGoal::class, 'performance_goal_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
