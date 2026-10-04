<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementSurveyResponse extends Model
{
    protected $fillable = [
        'engagement_survey_id',
        'employee_profile_id',
        'response_number',
        'engagement_score',
        'enps_score',
        'sentiment',
        'dimension_scores',
        'comment',
        'status',
        'submitted_at',
        'created_by',
    ];

    protected $casts = [
        'engagement_score' => 'integer',
        'enps_score' => 'integer',
        'dimension_scores' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(EngagementSurvey::class, 'engagement_survey_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
