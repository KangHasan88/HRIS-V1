<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalWorkforceVendorInvoice extends Model
{
    protected $fillable = [
        'external_workforce_vendor_id',
        'external_workforce_contract_id',
        'company_id',
        'branch_id',
        'cost_center_id',
        'invoice_number',
        'vendor_invoice_number',
        'period_start',
        'period_end',
        'invoice_date',
        'due_date',
        'source_scope',
        'claimed_amount',
        'system_amount',
        'variance_amount',
        'tax_amount',
        'payable_amount',
        'reconciliation_status',
        'approval_status',
        'payment_status',
        'reviewed_by',
        'reviewed_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'reviewed_at' => 'datetime',
        'claimed_amount' => 'decimal:2',
        'system_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceVendor::class, 'external_workforce_vendor_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceContract::class, 'external_workforce_contract_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ExternalWorkforceVendorInvoiceLine::class);
    }
}
