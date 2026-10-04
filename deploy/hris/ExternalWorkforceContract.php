<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalWorkforceContract extends Model
{
    protected $fillable = [
        'external_workforce_vendor_id',
        'company_id',
        'branch_id',
        'cost_center_id',
        'contract_number',
        'contract_title',
        'workforce_scope',
        'worker_type_scope',
        'start_date',
        'end_date',
        'renewal_reminder_date',
        'headcount_quota',
        'monthly_service_fee',
        'daily_rate',
        'overtime_rate',
        'sla_summary',
        'document_reference',
        'location_scope',
        'responsibility_matrix',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_reminder_date' => 'date',
        'headcount_quota' => 'integer',
        'monthly_service_fee' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'location_scope' => 'array',
        'responsibility_matrix' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceVendor::class, 'external_workforce_vendor_id');
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

    public function workers(): HasMany
    {
        return $this->hasMany(ExternalWorkforceWorker::class);
    }

    public function vendorInvoices(): HasMany
    {
        return $this->hasMany(ExternalWorkforceVendorInvoice::class);
    }
}
