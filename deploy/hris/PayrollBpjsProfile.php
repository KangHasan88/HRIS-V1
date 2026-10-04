<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollBpjsProfile extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'tax_profile_id',
        'bpjs_kesehatan_number',
        'bpjs_ketenagakerjaan_number',
        'bpjs_class',
        'effective_date',
        'health_contribution_base',
        'employment_contribution_base',
        'health_employee_rate',
        'health_employer_rate',
        'jht_employee_rate',
        'jht_employer_rate',
        'jp_employee_rate',
        'jp_employer_rate',
        'is_active',
        'verification_status',
        'notes',
        'calculation_snapshot',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'health_contribution_base' => 'decimal:2',
        'employment_contribution_base' => 'decimal:2',
        'health_employee_rate' => 'decimal:4',
        'health_employer_rate' => 'decimal:4',
        'jht_employee_rate' => 'decimal:4',
        'jht_employer_rate' => 'decimal:4',
        'jp_employee_rate' => 'decimal:4',
        'jp_employer_rate' => 'decimal:4',
        'is_active' => 'boolean',
        'calculation_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function taxProfile(): BelongsTo
    {
        return $this->belongsTo(TaxProfile::class);
    }
}
