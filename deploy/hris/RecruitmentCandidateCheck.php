<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentCandidateCheck extends Model
{
    protected $fillable = [
        'recruitment_candidate_id',
        'check_number',
        'check_type',
        'vendor_name',
        'reference_name',
        'reference_contact',
        'requested_at',
        'due_at',
        'completed_at',
        'status',
        'result',
        'is_blocking',
        'findings',
        'evidence_links',
        'created_by',
    ];

    protected $casts = [
        'requested_at' => 'date',
        'due_at' => 'date',
        'completed_at' => 'date',
        'is_blocking' => 'boolean',
        'evidence_links' => 'array',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'recruitment_candidate_id');
    }
}
