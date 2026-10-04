<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompensationAccessPolicy extends Model
{
    protected $fillable = [
        'policy_name',
        'role_scope',
        'access_level',
        'can_view_salary',
        'can_edit_salary',
        'can_export_salary',
        'requires_approval',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'can_view_salary' => 'boolean',
        'can_edit_salary' => 'boolean',
        'can_export_salary' => 'boolean',
        'requires_approval' => 'boolean',
    ];
}
