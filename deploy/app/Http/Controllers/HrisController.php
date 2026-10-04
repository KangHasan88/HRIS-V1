<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HrisController extends Controller
{
    public function index(): View
    {
        return view('hris.dashboard', [
            'active' => 'dashboard',
            ...$this->sharedData(),
        ]);
    }

    public function ess(): View
    {
        return view('hris.dashboard', [
            'active' => 'ess',
            ...$this->sharedData(),
        ]);
    }

    public function employees(): View
    {
        return view('hris.dashboard', [
            'active' => 'employees',
            ...$this->sharedData(),
        ]);
    }

    public function attendance(): View
    {
        return view('hris.dashboard', [
            'active' => 'attendance',
            ...$this->sharedData(),
        ]);
    }

    public function leave(): View
    {
        return view('hris.dashboard', [
            'active' => 'leave',
            ...$this->sharedData(),
        ]);
    }

    public function payroll(): View
    {
        return view('hris.dashboard', [
            'active' => 'payroll',
            ...$this->sharedData(),
        ]);
    }

    public function documents(): View
    {
        return view('hris.dashboard', [
            'active' => 'documents',
            ...$this->sharedData(),
        ]);
    }

    private function sharedData(): array
    {
        $employees = [
            ['name' => 'Ayu Prameswari', 'role' => 'People Operations Lead', 'team' => 'HR', 'status' => 'Permanent', 'location' => 'Jakarta', 'joined' => '2021-02-15', 'avatar' => 'AP'],
            ['name' => 'Bima Santoso', 'role' => 'Backend Engineer', 'team' => 'Engineering', 'status' => 'Permanent', 'location' => 'Bandung', 'joined' => '2022-07-04', 'avatar' => 'BS'],
            ['name' => 'Citra Lestari', 'role' => 'Finance Analyst', 'team' => 'Finance', 'status' => 'Contract', 'location' => 'Surabaya', 'joined' => '2023-01-09', 'avatar' => 'CL'],
            ['name' => 'Dimas Wirawan', 'role' => 'Sales Manager', 'team' => 'Commercial', 'status' => 'Permanent', 'location' => 'Jakarta', 'joined' => '2020-11-18', 'avatar' => 'DW'],
            ['name' => 'Nadia Putri', 'role' => 'Product Designer', 'team' => 'Product', 'status' => 'Probation', 'location' => 'Yogyakarta', 'joined' => '2026-04-20', 'avatar' => 'NP'],
        ];

        return [
            'employee' => [
                'name' => 'Ayu Prameswari',
                'role' => 'People Operations Lead',
                'employee_id' => 'KRM-0018',
                'department' => 'Human Resources',
                'manager' => 'Raka Mahendra',
                'leave_balance' => 9,
                'attendance_rate' => 97,
                'payroll_status' => 'Ready for review',
            ],
            'stats' => [
                ['label' => 'Active Employees', 'value' => '128', 'trend' => '+8 this quarter'],
                ['label' => 'Attendance Today', 'value' => '94%', 'trend' => '7 late check-ins'],
                ['label' => 'Leave Requests', 'value' => '18', 'trend' => '6 awaiting approval'],
                ['label' => 'Payroll Batch', 'value' => 'May', 'trend' => 'Rp 1.42B gross'],
            ],
            'employees' => $employees,
            'leaveRequests' => [
                ['name' => 'Bima Santoso', 'type' => 'Annual Leave', 'dates' => 'Jun 10-12, 2026', 'days' => 3, 'status' => 'Pending'],
                ['name' => 'Citra Lestari', 'type' => 'Sick Leave', 'dates' => 'Jun 3, 2026', 'days' => 1, 'status' => 'Approved'],
                ['name' => 'Dimas Wirawan', 'type' => 'Business Trip', 'dates' => 'Jun 17-19, 2026', 'days' => 3, 'status' => 'Review'],
            ],
            'attendance' => [
                ['name' => 'Ayu Prameswari', 'clock_in' => '08:17', 'clock_out' => '17:45', 'mode' => 'Office', 'status' => 'On time'],
                ['name' => 'Bima Santoso', 'clock_in' => '09:08', 'clock_out' => '-', 'mode' => 'Remote', 'status' => 'Late'],
                ['name' => 'Nadia Putri', 'clock_in' => '08:02', 'clock_out' => '17:11', 'mode' => 'Office', 'status' => 'On time'],
                ['name' => 'Citra Lestari', 'clock_in' => '-', 'clock_out' => '-', 'mode' => 'Leave', 'status' => 'Approved leave'],
            ],
            'payroll' => [
                ['component' => 'Basic Salary', 'amount' => 'Rp 980,000,000', 'note' => '128 employees'],
                ['component' => 'Allowances', 'amount' => 'Rp 215,500,000', 'note' => 'Transport, meal, remote'],
                ['component' => 'BPJS & Tax', 'amount' => 'Rp 188,400,000', 'note' => 'Calculated'],
                ['component' => 'Reimbursements', 'amount' => 'Rp 36,250,000', 'note' => '12 pending proofs'],
            ],
            'documents' => [
                ['title' => 'Employee Handbook 2026', 'owner' => 'HR Policy', 'status' => 'Published'],
                ['title' => 'PKWT Template', 'owner' => 'Legal', 'status' => 'Needs review'],
                ['title' => 'Remote Work Agreement', 'owner' => 'People Ops', 'status' => 'Published'],
            ],
            'timeline' => [
                ['time' => '08:00', 'title' => 'Attendance sync completed', 'detail' => '112 records imported from biometric device.'],
                ['time' => '10:30', 'title' => 'Payroll anomaly detected', 'detail' => '3 allowance values need HR review.'],
                ['time' => '14:00', 'title' => 'Onboarding task due', 'detail' => 'Nadia Putri needs laptop handover confirmation.'],
            ],
            'quickActions' => [
                'Request leave',
                'Clock in/out',
                'Upload reimbursement',
                'Download payslip',
                'Update profile',
                'Open HR ticket',
            ],
        ];
    }
}
