<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBpjsProfile extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'bpjs_kesehatan_number',
        'bpjs_ketenagakerjaan_number',
        'bpjs_class',
        'registered_at',
        'coverage_snapshot',
    ];

    protected $casts = [
        'registered_at' => 'date',
        'coverage_snapshot' => 'array',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
