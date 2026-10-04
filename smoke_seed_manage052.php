<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmployeeProfile;
use App\Models\LeavePolicyType;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$manager = User::where('email', 'bima@kurmigo.id')->firstOrFail();
$company = Company::where('code', 'BMP')->firstOrFail();
$branch = Branch::where('code', 'BMP-SITE-01')->first() ?: Branch::query()->firstOrFail();

EmployeeProfile::updateOrCreate(
    ['employee_number' => 'KRM-MGR-SMOKE-052'],
    [
        'user_id' => $manager->id,
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'full_name' => 'Bima Manager Smoke',
        'gender' => 'male',
        'employment_status' => 'permanent',
        'position' => 'Manager Operasional',
        'department' => 'Operasional Site',
        'join_date' => '2026-01-01',
        'work_email' => 'bima@kurmigo.id',
        'is_active' => true,
        'created_by' => $manager->id,
    ]
);

$employee = EmployeeProfile::updateOrCreate(
    ['employee_number' => 'KRM-TEAM-SMOKE-052'],
    [
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'full_name' => 'Nadia Approval Smoke',
        'gender' => 'female',
        'employment_status' => 'permanent',
        'position' => 'Staff Operasional',
        'department' => 'Operasional Site',
        'join_date' => '2026-01-01',
        'work_email' => 'nadia.approval.smoke052@kurmigo.id',
        'is_active' => true,
        'created_by' => $manager->id,
    ]
);

$leaveType = LeavePolicyType::firstOrCreate(
    ['code' => 'ANNUAL-SMOKE-052'],
    [
        'name' => 'Cuti Tahunan Smoke Approval',
        'category' => 'annual',
        'annual_quota_days' => 12,
        'status' => 'active',
        'created_by' => $manager->id,
    ]
);

$leave = LeaveRequest::updateOrCreate(
    ['request_number' => 'LV-SMOKE-052'],
    [
        'employee_profile_id' => $employee->id,
        'leave_policy_type_id' => $leaveType->id,
        'start_date' => '2026-07-22',
        'end_date' => '2026-07-26',
        'requested_days' => 5,
        'balance_before' => 12,
        'balance_after' => 7,
        'request_source' => 'ess_webview',
        'approval_status' => 'submitted',
        'current_approver' => 'Bima Manager Smoke',
        'submitted_at' => now()->subDays(3),
        'created_by' => $manager->id,
    ]
);

$overtime = OvertimeRequest::updateOrCreate(
    ['request_number' => 'OT-SMOKE-052'],
    [
        'employee_profile_id' => $employee->id,
        'overtime_date' => '2026-07-22',
        'start_at' => '2026-07-22 18:00:00',
        'end_at' => '2026-07-22 21:00:00',
        'requested_hours' => 3,
        'estimated_amount' => 350000,
        'overtime_type' => 'regular_day',
        'request_source' => 'ess_webview',
        'approval_status' => 'submitted',
        'current_approver' => 'Bima Manager Smoke',
        'submitted_at' => now(),
        'created_by' => $manager->id,
    ]
);

echo json_encode(['leave' => $leave->id, 'overtime' => $overtime->id]);
