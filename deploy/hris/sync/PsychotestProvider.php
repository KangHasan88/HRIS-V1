<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PsychotestProvider extends Model
{
    protected $fillable = [
        'company_id',
        'provider_code',
        'provider_name',
        'provider_type',
        'base_url',
        'auth_method',
        'credential_reference',
        'webhook_url',
        'callback_secret_reference',
        'sla_result_hours',
        'owner_name',
        'connection_status',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(PsychotestPackage::class);
    }

    public function invitations(): HasManyThrough
    {
        return $this->hasManyThrough(PsychotestInvitation::class, PsychotestPackage::class);
    }
}
