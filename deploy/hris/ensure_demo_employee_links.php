<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$company = Company::firstOrCreate(
    ['code' => 'BMP'],
    ['name' => 'BMP', 'legal_name' => 'BMP', 'status' => 'active']
);

$branch = Branch::firstOrCreate(
    ['company_id' => $company->id, 'code' => 'BMP-SITE-01'],
    ['name' => 'Site Operasional 01', 'branch_type' => 'site', 'status' => 'active']
);

$profiles = [
    'bima@kurmigo.id' => [
        'employee_number' => 'KRM-0004',
        'full_name' => 'Bima Santoso',
        'position' => 'Supervisor Operasional',
    ],
    'dimas@kurmigo.id' => [
        'employee_number' => 'KRM-0005',
        'full_name' => 'Dimas Wirawan',
        'position' => 'Staff Operasional',
    ],
    'nadia@kurmigo.id' => [
        'employee_number' => 'KRM-0006',
        'full_name' => 'Nadia Putri',
        'position' => 'Staff Operasional',
    ],
];

foreach ($profiles as $email => $data) {
    $user = User::where('email', $email)->first();
    if (! $user) {
        echo "skip {$email}: user not found" . PHP_EOL;
        continue;
    }

    $employee = EmployeeProfile::firstOrNew(['employee_number' => $data['employee_number']]);
    $employee->fill([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'full_name' => $data['full_name'],
        'employment_status' => 'permanent',
        'position' => $data['position'],
        'work_email' => $email,
        'is_active' => true,
    ]);
    $employee->save();

    echo "linked {$email} => {$employee->employee_number}" . PHP_EOL;
}
