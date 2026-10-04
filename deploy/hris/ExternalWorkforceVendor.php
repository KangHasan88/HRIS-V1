<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalWorkforceVendor extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'vendor_code',
        'vendor_name',
        'vendor_type',
        'npwp',
        'nib',
        'pic_name',
        'pic_phone',
        'pic_email',
        'address',
        'compliance_snapshot',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'compliance_snapshot' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ExternalWorkforceContract::class);
    }

    public function workers(): HasMany
    {
        return $this->hasMany(ExternalWorkforceWorker::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ExternalWorkforceVendorInvoice::class);
    }
}
