<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrHelpdeskTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'company_id',
        'branch_id',
        'ticket_number',
        'category',
        'priority',
        'subject',
        'description',
        'attachment_reference',
        'status',
        'channel',
        'first_response_due_at',
        'sla_due_at',
        'responded_at',
        'resolved_at',
        'assigned_to',
        'created_by',
        'closed_by',
        'metadata',
    ];

    protected $casts = [
        'first_response_due_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'responded_at' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(HrHelpdeskMessage::class);
    }
}
