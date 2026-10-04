<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeavePolicyType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'annual_quota_days',
        'max_days_per_request',
        'requires_balance',
        'is_paid',
        'requires_attachment',
        'approval_required',
        'carry_forward_allowed',
        'carry_forward_max_days',
        'carry_forward_expiry_months',
        'negative_balance_allowed',
        'prorate_on_join',
        'gender_rule',
        'min_service_months',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'annual_quota_days' => 'decimal:2',
        'carry_forward_max_days' => 'decimal:2',
        'requires_balance' => 'boolean',
        'is_paid' => 'boolean',
        'requires_attachment' => 'boolean',
        'approval_required' => 'boolean',
        'carry_forward_allowed' => 'boolean',
        'negative_balance_allowed' => 'boolean',
        'prorate_on_join' => 'boolean',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(LeavePolicyRule::class);
    }
}
