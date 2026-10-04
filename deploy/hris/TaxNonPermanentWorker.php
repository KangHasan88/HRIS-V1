<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxNonPermanentWorker extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'worker_name',
        'worker_number',
        'nik',
        'npwp',
        'worker_type',
        'ptkp_status',
        'is_npwp_verified',
        'is_active',
    ];

    protected $casts = [
        'is_npwp_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function calculations(): HasMany
    {
        return $this->hasMany(TaxNonPermanentCalculation::class);
    }
}
