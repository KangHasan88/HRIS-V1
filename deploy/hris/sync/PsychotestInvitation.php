<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PsychotestInvitation extends Model
{
    protected $fillable = [
        'recruitment_candidate_id',
        'psychotest_package_id',
        'invitation_number',
        'token',
        'delivery_channel',
        'sent_at',
        'opened_at',
        'started_at',
        'consented_at',
        'completed_at',
        'expires_at',
        'status',
        'result_sync_status',
        'instruction',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'started_at' => 'datetime',
        'consented_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'recruitment_candidate_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PsychotestPackage::class, 'psychotest_package_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(PsychotestResult::class);
    }
}
