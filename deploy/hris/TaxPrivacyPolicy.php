<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxPrivacyPolicy extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'mask_nik',
        'mask_npwp',
        'export_requires_approval',
        'retention_years',
        'legal_basis',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'mask_nik' => 'boolean',
        'mask_npwp' => 'boolean',
        'export_requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
