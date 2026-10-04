<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRegressionCase extends Model
{
    protected $fillable = [
        'case_number',
        'case_type',
        'case_name',
        'period',
        'input_payload',
        'expected_payload',
        'actual_payload',
        'expected_pph21_amount',
        'actual_pph21_amount',
        'variance_amount',
        'coretax_schema_version',
        'coretax_validation_status',
        'run_status',
        'assertion_snapshot',
        'last_run_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'input_payload' => 'array',
        'expected_payload' => 'array',
        'actual_payload' => 'array',
        'expected_pph21_amount' => 'decimal:2',
        'actual_pph21_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'assertion_snapshot' => 'array',
        'last_run_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
