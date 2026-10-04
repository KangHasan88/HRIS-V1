<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerTeamDocumentRequest extends Model
{
    protected $fillable = [
        'manager_user_id',
        'employee_profile_id',
        'request_number',
        'document_type',
        'purpose',
        'needed_date',
        'delivery_channel',
        'approval_status',
        'current_approver',
        'request_note',
        'hr_note',
        'created_by',
    ];

    protected $casts = [
        'needed_date' => 'date',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
