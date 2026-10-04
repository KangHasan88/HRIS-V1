<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\PayrollAdjustment;
use Illuminate\View\View;

class EssPayrollAdjustmentController extends Controller
{
    public function index(): View
    {
        $employee = EmployeeProfile::where('user_id', auth()->id())
            ->orWhere('work_email', auth()->user()?->email)
            ->first();

        $adjustments = collect();
        if ($employee) {
            $adjustments = PayrollAdjustment::where('employee_profile_id', $employee->id)
                ->whereIn('status', ['approved', 'posted'])
                ->latest()
                ->limit(50)
                ->get();
        }

        return view('ess.payroll-adjustments', [
            'active' => 'ess-payroll-adjustments',
            'employee' => $employee,
            'adjustments' => $adjustments,
            'summary' => [
                'total' => $adjustments->count(),
                'posted' => $adjustments->where('status', 'posted')->count(),
                'net' => (float) $adjustments->sum(fn ($item) => $item->impact_snapshot['net_impact'] ?? 0),
            ],
        ]);
    }
}
