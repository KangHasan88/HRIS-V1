<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxCorrection extends Model
{
    protected $fillable = [
        'tax_profile_id',
        'tax_monthly_report_package_id',
        'company_id',
        'correction_number',
        'correction_type',
        'period',
        'payroll_period',
        'previous_gross_income',
        'corrected_gross_income',
        'previous_tax_base',
        'corrected_tax_base',
        'previous_pph21_amount',
        'corrected_pph21_amount',
        'delta_pph21_amount',
        'payroll_adjustment_type',
        'status',
        'reason',
        'before_snapshot',
        'after_snapshot',
        'workflow_snapshot',
        'approved_at',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'previous_gross_income' => 'decimal:2',
        'corrected_gross_income' => 'decimal:2',
        'previous_tax_base' => 'decimal:2',
        'corrected_tax_base' => 'decimal:2',
        'previous_pph21_amount' => 'decimal:2',
        'corrected_pph21_amount' => 'decimal:2',
        'delta_pph21_amount' => 'decimal:2',
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
        'workflow_snapshot' => 'array',
        'approved_at' => 'datetime',
    ];

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TaxMonthlyReportPackage::class, 'tax_monthly_report_package_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
