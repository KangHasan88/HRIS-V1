<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceGoal extends Model
{
    protected $fillable = [
        'performance_goal_cycle_id',
        'employee_profile_id',
        'manager_user_id',
        'parent_goal_id',
        'goal_number',
        'goal_type',
        'objective_title',
        'objective_description',
        'key_result_metric',
        'baseline_value',
        'target_value',
        'current_value',
        'weight',
        'progress_percent',
        'score',
        'confidence_level',
        'status',
        'cascading_snapshot',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'weight' => 'decimal:2',
        'progress_percent' => 'decimal:2',
        'score' => 'decimal:2',
        'cascading_snapshot' => 'array',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceGoalCycle::class, 'performance_goal_cycle_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function parentGoal(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_goal_id');
    }

    public function childGoals(): HasMany
    {
        return $this->hasMany(self::class, 'parent_goal_id');
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(PerformanceGoalCheckIn::class);
    }
}
