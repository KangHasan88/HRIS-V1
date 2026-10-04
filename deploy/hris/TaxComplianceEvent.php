<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxComplianceEvent extends Model
{
    protected $fillable = [
        'company_id',
        'event_number',
        'event_type',
        'title',
        'period',
        'due_date',
        'reminder_days_before',
        'reminder_date',
        'sla_level',
        'status',
        'owner_user_id',
        'escalation_user_id',
        'completed_at',
        'escalated_at',
        'checklist_snapshot',
        'notification_snapshot',
        'escalation_snapshot',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_date' => 'date',
        'completed_at' => 'datetime',
        'escalated_at' => 'datetime',
        'checklist_snapshot' => 'array',
        'notification_snapshot' => 'array',
        'escalation_snapshot' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function escalationUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalation_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
