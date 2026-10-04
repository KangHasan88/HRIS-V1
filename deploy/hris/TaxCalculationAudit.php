<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxCalculationAudit extends Model
{
    protected $fillable = [
        'tax_profile_id',
        'company_id',
        'branch_id',
        'period',
        'gross_income',
        'taxable_allowance',
        'deduction',
        'net_income',
        'annualized_income',
        'ptkp_amount',
        'pkp_amount',
        'pph21_amount',
        'tax_method',
        'calculation_status',
        'calculation_steps',
        'source_snapshot',
        'created_by',
    ];

    protected $casts = [
        'gross_income' => 'decimal:2',
        'taxable_allowance' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_income' => 'decimal:2',
        'annualized_income' => 'decimal:2',
        'ptkp_amount' => 'decimal:2',
        'pkp_amount' => 'decimal:2',
        'pph21_amount' => 'decimal:2',
        'calculation_steps' => 'array',
        'source_snapshot' => 'array',
    ];

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
