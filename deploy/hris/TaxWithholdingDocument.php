<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxWithholdingDocument extends Model
{
    protected $fillable = [
        'tax_profile_id',
        'tax_monthly_report_package_id',
        'company_id',
        'document_number',
        'document_type',
        'period',
        'status',
        'gross_income',
        'tax_base',
        'pph21_amount',
        'employee_snapshot',
        'document_payload',
        'checksum',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'gross_income' => 'decimal:2',
        'tax_base' => 'decimal:2',
        'pph21_amount' => 'decimal:2',
        'employee_snapshot' => 'array',
        'document_payload' => 'array',
        'published_at' => 'datetime',
    ];

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TaxMonthlyReportPackage::class, 'tax_monthly_report_package_id');
    }
}
