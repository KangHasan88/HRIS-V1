<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $fillable = [
        'company_id',
        'run_number',
        'period',
        'cutoff_start',
        'cutoff_end',
        'run_type',
        'status',
        'employee_count',
        'validation_passed',
        'validation_failed',
        'anomaly_count',
        'gross_estimate',
        'deduction_estimate',
        'net_estimate',
        'source_snapshot',
        'notes',
        'validated_at',
        'created_by',
    ];

    protected $casts = [
        'cutoff_start' => 'date',
        'cutoff_end' => 'date',
        'validated_at' => 'datetime',
        'source_snapshot' => 'array',
        'gross_estimate' => 'decimal:2',
        'deduction_estimate' => 'decimal:2',
        'net_estimate' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function validations(): HasMany
    {
        return $this->hasMany(PayrollRunValidation::class);
    }

    public function anomalies(): HasMany
    {
        return $this->hasMany(PayrollRunAnomaly::class);
    }
}
