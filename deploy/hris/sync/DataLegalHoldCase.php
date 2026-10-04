<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataLegalHoldCase extends Model
{
    protected $fillable = [
        'data_retention_policy_id', 'company_id', 'case_number', 'case_title',
        'data_domain', 'scope_reference', 'reason_category', 'hold_start_date',
        'hold_end_date', 'custodian_name', 'approval_status', 'status',
        'evidence_snapshot', 'notes', 'created_by',
    ];

    protected $casts = [
        'hold_start_date' => 'date',
        'hold_end_date' => 'date',
        'evidence_snapshot' => 'array',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(DataRetentionPolicy::class, 'data_retention_policy_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(DataRetentionExecutionLog::class);
    }
}
