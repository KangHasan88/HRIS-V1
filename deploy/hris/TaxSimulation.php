<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxSimulation extends Model
{
    protected $fillable = [
        'tax_profile_id',
        'company_id',
        'simulation_number',
        'scenario_name',
        'scenario_type',
        'period',
        'tax_method',
        'gross_income',
        'taxable_allowance',
        'irregular_income',
        'deduction',
        'tax_base',
        'ter_rate',
        'pph21_amount',
        'tax_allowance_amount',
        'company_tax_borne_amount',
        'final_tax_amount',
        'gross_up_iterations',
        'take_home_pay',
        'scenario_status',
        'visible_to_employee',
        'approved_at',
        'approved_by',
        'input_snapshot',
        'result_snapshot',
        'comparison_snapshot',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'gross_income' => 'decimal:2',
        'taxable_allowance' => 'decimal:2',
        'irregular_income' => 'decimal:2',
        'deduction' => 'decimal:2',
        'tax_base' => 'decimal:2',
        'ter_rate' => 'decimal:5',
        'pph21_amount' => 'decimal:2',
        'tax_allowance_amount' => 'decimal:2',
        'company_tax_borne_amount' => 'decimal:2',
        'final_tax_amount' => 'decimal:2',
        'take_home_pay' => 'decimal:2',
        'visible_to_employee' => 'boolean',
        'approved_at' => 'datetime',
        'input_snapshot' => 'array',
        'result_snapshot' => 'array',
        'comparison_snapshot' => 'array',
    ];

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
