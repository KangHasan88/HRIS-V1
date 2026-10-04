<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxMonthlyCalculation extends Model
{
    protected $fillable = [
        'tax_profile_id',
        'tax_ter_rate_id',
        'company_id',
        'branch_id',
        'period',
        'ter_category',
        'tax_method',
        'gross_income',
        'taxable_allowance',
        'deduction',
        'ter_base_amount',
        'ter_rate',
        'pph21_amount',
        'tax_allowance_amount',
        'company_tax_borne_amount',
        'take_home_pay',
        'calculation_status',
        'calculation_steps',
        'source_snapshot',
        'created_by',
    ];

    protected $casts = [
        'gross_income' => 'decimal:2',
        'taxable_allowance' => 'decimal:2',
        'deduction' => 'decimal:2',
        'ter_base_amount' => 'decimal:2',
        'ter_rate' => 'decimal:6',
        'pph21_amount' => 'decimal:2',
        'tax_allowance_amount' => 'decimal:2',
        'company_tax_borne_amount' => 'decimal:2',
        'take_home_pay' => 'decimal:2',
        'calculation_steps' => 'array',
        'source_snapshot' => 'array',
    ];

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }

    public function terRate(): BelongsTo
    {
        return $this->belongsTo(TaxTerRate::class, 'tax_ter_rate_id');
    }
}
