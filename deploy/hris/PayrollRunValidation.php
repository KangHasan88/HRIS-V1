<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRunValidation extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'check_code',
        'check_name',
        'severity',
        'status',
        'affected_count',
        'message',
        'sample_payload',
    ];

    protected $casts = [
        'sample_payload' => 'array',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }
}
