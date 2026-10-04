<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflowStep extends Model
{
    protected $fillable = [
        'approval_workflow_id',
        'step_order',
        'step_name',
        'approver_type',
        'approver_value',
        'routing_condition',
        'sla_hours',
        'is_parallel',
        'can_delegate',
        'notification_channels',
    ];

    protected $casts = [
        'is_parallel' => 'boolean',
        'can_delegate' => 'boolean',
        'notification_channels' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }
}
