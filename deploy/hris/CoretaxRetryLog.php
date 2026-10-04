<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoretaxRetryLog extends Model
{
    protected $fillable = [
        'coretax_connector_setting_id',
        'tax_monthly_export_template_id',
        'request_type',
        'status',
        'attempt',
        'max_retry',
        'next_retry_at',
        'endpoint',
        'payload_checksum',
        'response_code',
        'response_message',
        'request_snapshot',
        'response_snapshot',
        'created_by',
    ];

    protected $casts = [
        'next_retry_at' => 'datetime',
        'request_snapshot' => 'array',
        'response_snapshot' => 'array',
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(CoretaxConnectorSetting::class, 'coretax_connector_setting_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaxMonthlyExportTemplate::class, 'tax_monthly_export_template_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CoretaxSubmissionTracker::class);
    }
}
