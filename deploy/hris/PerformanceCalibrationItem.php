<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceCalibrationItem extends Model
{
    protected $fillable = [
        'performance_calibration_session_id',
        'performance_review_form_id',
        'employee_profile_id',
        'original_score',
        'calibrated_score',
        'original_rating',
        'calibrated_rating',
        'rating_bucket',
        'calibration_decision',
        'decision_reason',
        'evidence_snapshot',
        'status',
        'created_by',
    ];

    protected $casts = [
        'original_score' => 'decimal:2',
        'calibrated_score' => 'decimal:2',
        'evidence_snapshot' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PerformanceCalibrationSession::class, 'performance_calibration_session_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(PerformanceReviewForm::class, 'performance_review_form_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
