<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentInterview extends Model
{
    protected $fillable = [
        'recruitment_candidate_id',
        'interview_number',
        'interview_stage',
        'scheduled_at',
        'duration_minutes',
        'interview_mode',
        'location_or_link',
        'interviewer_name',
        'interviewer_role',
        'status',
        'communication_score',
        'technical_score',
        'culture_score',
        'experience_score',
        'overall_score',
        'recommendation',
        'feedback_notes',
        'scorecard_snapshot',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'communication_score' => 'integer',
        'technical_score' => 'integer',
        'culture_score' => 'integer',
        'experience_score' => 'integer',
        'overall_score' => 'decimal:2',
        'scorecard_snapshot' => 'array',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'recruitment_candidate_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
