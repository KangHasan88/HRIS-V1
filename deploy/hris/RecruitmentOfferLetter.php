<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentOfferLetter extends Model
{
    protected $fillable = [
        'recruitment_candidate_id',
        'offer_number',
        'position_title',
        'join_date',
        'salary_offer',
        'employment_type',
        'template_name',
        'status',
        'sent_at',
        'expires_at',
        'signed_at',
        'esign_provider',
        'esign_reference',
        'signer_email',
        'offer_summary',
        'tracking_events',
        'created_by',
    ];

    protected $casts = [
        'join_date' => 'date',
        'salary_offer' => 'decimal:2',
        'sent_at' => 'date',
        'expires_at' => 'date',
        'signed_at' => 'date',
        'tracking_events' => 'array',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'recruitment_candidate_id');
    }
}
