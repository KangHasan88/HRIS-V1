<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricSyncLog extends Model
{
    protected $fillable = [
        'biometric_connector_id',
        'attendance_import_batch_id',
        'sync_number',
        'started_at',
        'finished_at',
        'status',
        'pulled_rows',
        'matched_rows',
        'failed_rows',
        'payload_summary',
        'message',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'payload_summary' => 'array',
    ];

    public function connector(): BelongsTo
    {
        return $this->belongsTo(BiometricConnector::class, 'biometric_connector_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AttendanceImportBatch::class, 'attendance_import_batch_id');
    }
}
