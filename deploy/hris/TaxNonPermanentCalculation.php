<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxNonPermanentCalculation extends Model
{
    protected $fillable = [
        'tax_non_permanent_worker_id',
        'company_id',
        'branch_id',
        'period',
        'work_days',
        'daily_rate',
        'gross_income',
        'taxable_base',
        'tax_rate',
        'pph21_amount',
        'calculation_status',
        'calculation_steps',
        'source_snapshot',
        'created_by',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'gross_income' => 'decimal:2',
        'taxable_base' => 'decimal:2',
        'tax_rate' => 'decimal:6',
        'pph21_amount' => 'decimal:2',
        'calculation_steps' => 'array',
        'source_snapshot' => 'array',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(TaxNonPermanentWorker::class, 'tax_non_permanent_worker_id');
    }
}
