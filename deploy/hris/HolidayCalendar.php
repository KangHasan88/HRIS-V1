<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayCalendar extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'holiday_date',
        'scope',
        'category',
        'religion',
        'region',
        'affects_attendance',
        'affects_leave',
        'affects_payroll',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'affects_attendance' => 'boolean',
        'affects_leave' => 'boolean',
        'affects_payroll' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
