<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalWorkforceVendorInvoiceLine extends Model
{
    protected $fillable = [
        'external_workforce_vendor_invoice_id',
        'external_workforce_worker_id',
        'source_type',
        'source_id',
        'work_date',
        'description',
        'quantity',
        'unit_name',
        'system_rate',
        'system_amount',
        'claimed_amount',
        'variance_amount',
        'reconciliation_status',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'quantity' => 'decimal:2',
        'system_rate' => 'decimal:2',
        'system_amount' => 'decimal:2',
        'claimed_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceVendorInvoice::class, 'external_workforce_vendor_invoice_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceWorker::class, 'external_workforce_worker_id');
    }
}
