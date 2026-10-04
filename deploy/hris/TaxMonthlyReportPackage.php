<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxMonthlyReportPackage extends Model
{
    protected $fillable = [
        'company_id',
        'period',
        'package_number',
        'status',
        'employee_count',
        'total_gross_income',
        'total_tax_base',
        'total_pph21',
        'summary_snapshot',
        'detail_snapshot',
        'validation_notes',
        'created_by',
    ];

    protected $casts = [
        'total_gross_income' => 'decimal:2',
        'total_tax_base' => 'decimal:2',
        'total_pph21' => 'decimal:2',
        'summary_snapshot' => 'array',
        'detail_snapshot' => 'array',
        'validation_notes' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function exportTemplates(): HasMany
    {
        return $this->hasMany(TaxMonthlyExportTemplate::class);
    }
}
