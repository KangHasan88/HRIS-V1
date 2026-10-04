<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDisbursementApproval extends Model
{
    protected $fillable = [
        'payroll_disbursement_batch_id', 'approval_step', 'approver_role',
        'approver_user_id', 'decision', 'decided_at', 'notes',
    ];

    protected $casts = ['decided_at' => 'datetime'];

    public function batch(): BelongsTo { return $this->belongsTo(PayrollDisbursementBatch::class, 'payroll_disbursement_batch_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approver_user_id'); }
}
