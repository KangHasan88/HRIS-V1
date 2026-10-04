<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRetentionExecutionLog extends Model
{
    protected $fillable = [
        'data_retention_policy_id', 'data_legal_hold_case_id', 'company_id',
        'execution_number', 'data_domain', 'cutoff_date', 'action_type',
        'records_scanned', 'records_affected', 'status', 'started_at',
        'completed_at', 'result_snapshot', 'notes', 'created_by',
    ];

    protected $casts = [
        'cutoff_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'result_snapshot' => 'array',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(DataRetentionPolicy::class, 'data_retention_policy_id');
    }

    public function legalHold(): BelongsTo
    {
        return $this->belongsTo(DataLegalHoldCase::class, 'data_legal_hold_case_id');
    }
}
