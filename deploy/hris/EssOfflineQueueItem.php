<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EssOfflineQueueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'queue_number',
        'device_uid',
        'device_name',
        'platform',
        'app_version',
        'item_type',
        'queue_status',
        'captured_at',
        'last_sync_attempt_at',
        'synced_at',
        'retry_count',
        'payload',
        'sync_result',
        'failure_reason',
        'created_by',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'last_sync_attempt_at' => 'datetime',
        'synced_at' => 'datetime',
        'payload' => 'array',
        'sync_result' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
