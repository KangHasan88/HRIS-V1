<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentCandidateDocument extends Model
{
    protected $fillable = [
        'recruitment_candidate_id',
        'document_type',
        'document_title',
        'file_reference',
        'verification_status',
        'submitted_at',
        'verified_by',
        'verified_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'submitted_at' => 'date',
        'verified_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'recruitment_candidate_id');
    }
}
