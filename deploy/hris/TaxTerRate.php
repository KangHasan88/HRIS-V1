<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxTerRate extends Model
{
    protected $fillable = [
        'category',
        'income_from',
        'income_to',
        'rate',
        'source_reference',
        'is_active',
    ];

    protected $casts = [
        'income_from' => 'decimal:2',
        'income_to' => 'decimal:2',
        'rate' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    public function calculations(): HasMany
    {
        return $this->hasMany(TaxMonthlyCalculation::class);
    }
}
