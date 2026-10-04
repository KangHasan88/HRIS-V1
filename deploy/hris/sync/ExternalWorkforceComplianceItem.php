<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalWorkforceComplianceItem extends Model
{
    protected $fillable = [
        'external_workforce_vendor_id',
        'external_workforce_contract_id',
        'item_number',
        'compliance_area',
        'requirement_name',
        'responsible_party',
        'document_reference',
        'issued_date',
        'expiry_date',
        'verification_status',
        'risk_level',
        'owner_name',
        'next_follow_up_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'next_follow_up_date' => 'date',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceVendor::class, 'external_workforce_vendor_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ExternalWorkforceContract::class, 'external_workforce_contract_id');
    }
}
