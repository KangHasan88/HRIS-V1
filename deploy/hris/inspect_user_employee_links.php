<?php

use App\Models\EmployeeProfile;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (User::with('role')->orderBy('id')->get(['id', 'name', 'email', 'role_id']) as $user) {
    $employee = EmployeeProfile::where('user_id', $user->id)
        ->orWhere('work_email', $user->email)
        ->first();

    echo implode(' | ', [
        $user->id,
        $user->email,
        'role=' . ($user->role?->name ?: '-'),
        'employee=' . ($employee?->employee_number ?: '-'),
    ]) . PHP_EOL;
}
