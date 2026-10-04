<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementActionPlan extends Model
{
    protected $fillable = [
        'engagement_survey_id',
        'action_number',
        'focus_area',
        'root_cause',
        'action_plan',
        'owner_name',
        'target_date',
        'progress_percent',
        'impact_metric',
        'priority',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'target_date' => 'date',
        'progress_percent' => 'integer',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(EngagementSurvey::class, 'engagement_survey_id');
    }
}
