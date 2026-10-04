<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxMonthlyExportTemplate extends Model
{
    protected $fillable = [
        'tax_monthly_report_package_id',
        'template_number',
        'schema_version',
        'format',
        'status',
        'columns_schema',
        'payload_snapshot',
        'checksum',
        'created_by',
    ];

    protected $casts = [
        'columns_schema' => 'array',
        'payload_snapshot' => 'array',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(TaxMonthlyReportPackage::class, 'tax_monthly_report_package_id');
    }
}
