<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceCalibrationSession extends Model
{
    protected $fillable = [
        'performance_review_cycle_id',
        'session_number',
        'session_name',
        'session_date',
        'facilitator_name',
        'calibration_scope',
        'target_distribution',
        'status',
        'rating_rules',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'session_date' => 'date',
        'rating_rules' => 'array',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceReviewCycle::class, 'performance_review_cycle_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PerformanceCalibrationItem::class);
    }
}
