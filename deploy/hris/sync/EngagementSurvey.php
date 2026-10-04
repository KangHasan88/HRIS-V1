<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EngagementSurvey extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'survey_number',
        'title',
        'survey_type',
        'audience_scope',
        'start_date',
        'end_date',
        'question_bank',
        'is_anonymous',
        'owner_name',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'question_bank' => 'array',
        'is_anonymous' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EngagementSurveyResponse::class);
    }

    public function actionPlans(): HasMany
    {
        return $this->hasMany(EngagementActionPlan::class);
    }
}
