<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentCandidate extends Model
{
    protected $fillable = [
        'recruitment_manpower_request_id',
        'candidate_number',
        'full_name',
        'email',
        'phone',
        'source_channel',
        'current_position',
        'current_company',
        'expected_salary',
        'pipeline_stage',
        'candidate_status',
        'portal_token',
        'portal_invited_at',
        'fit_score',
        'owner_recruiter',
        'applied_at',
        'last_touch_at',
        'stage_history',
        'tags',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expected_salary' => 'decimal:2',
        'fit_score' => 'integer',
        'applied_at' => 'date',
        'last_touch_at' => 'date',
        'portal_invited_at' => 'datetime',
        'stage_history' => 'array',
        'tags' => 'array',
    ];

    public function manpowerRequest(): BelongsTo
    {
        return $this->belongsTo(RecruitmentManpowerRequest::class, 'recruitment_manpower_request_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RecruitmentCandidateDocument::class, 'recruitment_candidate_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(RecruitmentInterview::class, 'recruitment_candidate_id');
    }

    public function offerLetters(): HasMany
    {
        return $this->hasMany(RecruitmentOfferLetter::class, 'recruitment_candidate_id');
    }

    public function checks(): HasMany
    {
        return $this->hasMany(RecruitmentCandidateCheck::class, 'recruitment_candidate_id');
    }

    public function employeeConversions(): HasMany
    {
        return $this->hasMany(RecruitmentEmployeeConversion::class, 'recruitment_candidate_id');
    }
}
