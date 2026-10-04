<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollComponent extends Model
{
    protected $fillable = [
        'company_id',
        'component_code',
        'component_name',
        'component_category',
        'calculation_method',
        'default_amount',
        'rate_value',
        'formula_expression',
        'dependencies',
        'prorate_basis',
        'is_taxable',
        'is_bpjs_basis',
        'is_payslip_visible',
        'gl_account',
        'effective_start',
        'effective_end',
        'status',
        'sort_order',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'rate_value' => 'decimal:6',
        'dependencies' => 'array',
        'is_taxable' => 'boolean',
        'is_bpjs_basis' => 'boolean',
        'is_payslip_visible' => 'boolean',
        'effective_start' => 'date',
        'effective_end' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(PayrollComponentRule::class);
    }
}
