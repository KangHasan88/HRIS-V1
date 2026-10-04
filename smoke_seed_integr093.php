<?php

use App\Models\BiometricConnector;
use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$company = Company::where('code', 'BMP')->first();
$branch = Branch::where('code', 'BMP-HO')->first() ?: Branch::query()->first();

$employee = EmployeeProfile::updateOrCreate(
    ['employee_number' => 'KRM-SMOKE-093'],
    [
        'company_id' => $company?->id,
        'branch_id' => $branch?->id,
        'full_name' => 'Smoke Agent Employee',
        'employment_status' => 'permanent',
        'position' => 'Staff Smoke',
        'work_email' => 'smoke.agent093@kurmigo.id',
        'is_active' => true,
    ]
);

$connector = BiometricConnector::updateOrCreate(
    ['connector_code' => 'BIO-AGENT-SMOKE'],
    [
        'company_id' => $company?->id,
        'branch_id' => $branch?->id,
        'connector_name' => 'Smoke Local Agent',
        'vendor' => 'generic',
        'connection_type' => 'local_agent',
        'endpoint_url' => 'C:\\AttendanceSmoke',
        'auth_mode' => 'local_agent',
        'agent_pairing_code' => 'BIO-AGENT-SMOKE-PAIR',
        'agent_token_hash' => Hash::make('smoke-agent-093'),
        'machine_code' => 'BIO-SMOKE-01',
        'machine_location' => 'Smoke Lab',
        'sync_interval_minutes' => 15,
        'status' => 'active',
        'agent_status' => 'waiting_first_sync',
        'mapping_config' => [
            'employee_field' => 'employee_number',
            'time_field' => 'event_time',
            'event_field' => 'event_type',
        ],
        'agent_config' => [
            'api_endpoint' => 'https://31.97.106.123:8091/api/attendance-agent/clock-events',
            'source_mode' => 'csv_folder',
        ],
    ]
);

echo json_encode(['employee' => $employee->employee_number, 'connector' => $connector->connector_code]);
