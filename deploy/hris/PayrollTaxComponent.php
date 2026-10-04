<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollTaxComponent extends Model
{
    protected $fillable = [
        'company_id',
        'component_code',
        'component_name',
        'component_type',
        'tax_treatment',
        'payroll_formula_key',
        'default_amount',
        'is_taxable',
        'is_recurring',
        'dependencies',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_recurring' => 'boolean',
        'dependencies' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
