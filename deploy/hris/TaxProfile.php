<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxProfile extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'employee_number',
        'employee_name',
        'nik',
        'npwp',
        'ptkp_status',
        'tax_method',
        'klu',
        'kpp_code',
        'kpp_name',
        'is_npwp_verified',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_npwp_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function calculationAudits(): HasMany
    {
        return $this->hasMany(TaxCalculationAudit::class);
    }

    public function monthlyCalculations(): HasMany
    {
        return $this->hasMany(TaxMonthlyCalculation::class);
    }

    public function annualReconciliations(): HasMany
    {
        return $this->hasMany(TaxAnnualReconciliation::class);
    }

    public function irregularIncomes(): HasMany
    {
        return $this->hasMany(TaxIrregularIncome::class);
    }
}
