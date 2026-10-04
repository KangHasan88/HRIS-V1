<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessReviewItem extends Model
{
    protected $fillable = [
        'access_review_id',
        'user_id',
        'role_id',
        'company_id',
        'legal_entity_id',
        'branch_id',
        'user_name',
        'user_email',
        'role_name',
        'current_permissions',
        'expected_permissions',
        'missing_permissions',
        'extra_permissions',
        'risk_flags',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'current_permissions' => 'array',
        'expected_permissions' => 'array',
        'missing_permissions' => 'array',
        'extra_permissions' => 'array',
        'risk_flags' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(AccessReview::class, 'access_review_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
