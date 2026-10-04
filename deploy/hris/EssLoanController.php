<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLoan;
use App\Models\EmployeeProfile;
use Illuminate\View\View;

class EssLoanController extends Controller
{
    public function index(): View
    {
        $employee = EmployeeProfile::where('user_id', auth()->id())
            ->orWhere('work_email', auth()->user()?->email)
            ->first();

        $loans = collect();
        if ($employee) {
            $loans = EmployeeLoan::with('installments')
                ->where('employee_profile_id', $employee->id)
                ->latest('loan_date')
                ->limit(50)
                ->get();
        }

        return view('ess.loans', [
            'active' => 'ess-loans',
            'employee' => $employee,
            'loans' => $loans,
            'summary' => [
                'total' => $loans->count(),
                'active' => $loans->where('payroll_posting_status', 'active')->count(),
                'outstanding' => (float) $loans->sum('outstanding_amount'),
                'next' => (float) $loans->flatMap->installments->where('status', 'scheduled')->sortBy('due_date')->first()?->scheduled_amount,
            ],
        ]);
    }
}
