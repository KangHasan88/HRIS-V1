<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychotestResult extends Model
{
    protected $fillable = [
        'psychotest_invitation_id',
        'recruitment_candidate_id',
        'psychotest_package_id',
        'result_number',
        'overall_score',
        'fit_recommendation',
        'risk_level',
        'dimension_scores',
        'raw_result',
        'summary',
        'reviewed_by',
        'reviewed_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'overall_score' => 'integer',
        'dimension_scores' => 'array',
        'raw_result' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(PsychotestInvitation::class, 'psychotest_invitation_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'recruitment_candidate_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PsychotestPackage::class, 'psychotest_package_id');
    }
}
