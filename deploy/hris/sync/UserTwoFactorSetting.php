<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTwoFactorSetting extends Model
{
    protected $fillable = [
        'user_id', 'security_two_factor_policy_id', 'method', 'secret_reference', 'secret_mask',
        'backup_code_masks', 'enabled_at', 'verified_at', 'last_challenged_at', 'status',
        'notes', 'created_by',
    ];

    protected $casts = [
        'backup_code_masks' => 'array',
        'enabled_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_challenged_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SecurityTwoFactorPolicy::class, 'security_two_factor_policy_id');
    }
}
