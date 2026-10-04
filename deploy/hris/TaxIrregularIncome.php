<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxIrregularIncome extends Model
{
    protected $fillable = [
        'tax_profile_id',
        'company_id',
        'branch_id',
        'period',
        'income_type',
        'tax_method',
        'gross_amount',
        'taxable_amount',
        'tax_rate',
        'pph21_amount',
        'net_amount',
        'calculation_status',
        'calculation_steps',
        'source_snapshot',
        'created_by',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_rate' => 'decimal:6',
        'pph21_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'calculation_steps' => 'array',
        'source_snapshot' => 'array',
    ];

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }
}
