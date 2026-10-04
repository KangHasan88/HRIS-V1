<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceReviewForm extends Model
{
    protected $fillable = [
        'performance_review_cycle_id',
        'employee_profile_id',
        'manager_user_id',
        'form_number',
        'review_scope',
        'status',
        'target_score',
        'final_score',
        'summary_note',
        'created_by',
    ];

    protected $casts = [
        'target_score' => 'decimal:2',
        'final_score' => 'decimal:2',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceReviewCycle::class, 'performance_review_cycle_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PerformanceReviewResponse::class);
    }
}
