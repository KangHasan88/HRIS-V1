<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFamilyMember extends Model
{
    protected $fillable = ['employee_profile_id', 'name', 'relationship', 'birth_date', 'is_dependent'];

    protected $casts = [
        'birth_date' => 'date',
        'is_dependent' => 'boolean',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
