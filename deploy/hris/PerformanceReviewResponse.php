<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReviewResponse extends Model
{
    protected $fillable = [
        'performance_review_form_id',
        'reviewer_user_id',
        'reviewer_employee_profile_id',
        'reviewer_type',
        'score',
        'competency_scores',
        'strength_feedback',
        'improvement_feedback',
        'development_suggestion',
        'status',
        'submitted_at',
        'created_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'competency_scores' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(PerformanceReviewForm::class, 'performance_review_form_id');
    }

    public function reviewerEmployeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'reviewer_employee_profile_id');
    }
}
