<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecurityTwoFactorPolicy extends Model
{
    protected $fillable = [
        'company_id', 'policy_code', 'policy_name', 'enforced_roles', 'privileged_actions',
        'otp_channel', 'require_on_login', 'require_for_privileged_action',
        'challenge_ttl_minutes', 'remember_device_days', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'enforced_roles' => 'array',
        'privileged_actions' => 'array',
        'require_on_login' => 'boolean',
        'require_for_privileged_action' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function userSettings(): HasMany
    {
        return $this->hasMany(UserTwoFactorSetting::class);
    }
}
