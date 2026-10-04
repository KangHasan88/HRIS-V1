<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalWorkforcePieceRate extends Model
{
    protected $fillable = [
        'external_workforce_vendor_id',
        'external_workforce_contract_id',
        'company_id',
        'branch_id',
        'cost_center_id',
        'rate_code',
        'work_name',
        'work_category',
        'unit_name',
        'rate_per_unit',
        'minimum_output',
        'quality_rule',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'rate_per_unit' => 'decimal:2',
        'minimum_output' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceVendor::class, 'external_workforce_vendor_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceContract::class, 'external_workforce_contract_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ExternalWorkforcePieceOutput::class);
    }
}
