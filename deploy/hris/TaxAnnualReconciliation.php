<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxAnnualReconciliation extends Model
{
    protected $fillable = [
        'tax_profile_id',
        'company_id',
        'branch_id',
        'tax_year',
        'annual_gross_income',
        'annual_taxable_allowance',
        'annual_deduction',
        'annual_net_income',
        'ptkp_amount',
        'pkp_amount',
        'annual_pph21_due',
        'monthly_pph21_paid',
        'true_up_amount',
        'true_up_status',
        'calculation_status',
        'monthly_snapshot',
        'calculation_steps',
        'created_by',
    ];

    protected $casts = [
        'annual_gross_income' => 'decimal:2',
        'annual_taxable_allowance' => 'decimal:2',
        'annual_deduction' => 'decimal:2',
        'annual_net_income' => 'decimal:2',
        'ptkp_amount' => 'decimal:2',
        'pkp_amount' => 'decimal:2',
        'annual_pph21_due' => 'decimal:2',
        'monthly_pph21_paid' => 'decimal:2',
        'true_up_amount' => 'decimal:2',
        'monthly_snapshot' => 'array',
        'calculation_steps' => 'array',
    ];

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }
}
