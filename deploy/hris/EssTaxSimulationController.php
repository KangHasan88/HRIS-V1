<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\TaxProfile;
use App\Models\TaxSimulation;
use Illuminate\View\View;

class EssTaxSimulationController extends Controller
{
    public function index(): View
    {
        $employee = EmployeeProfile::where('user_id', auth()->id())
            ->orWhere('work_email', auth()->user()?->email)
            ->first();

        $profile = null;
        if ($employee) {
            $profile = TaxProfile::where(function ($query) use ($employee) {
                $query->where('employee_number', $employee->employee_number)
                    ->orWhere('employee_name', $employee->full_name);
            })
                ->where('is_active', true)
                ->first();
        }

        $simulations = collect();
        if ($profile) {
            $simulations = TaxSimulation::where('tax_profile_id', $profile->id)
                ->where('visible_to_employee', true)
                ->where('scenario_status', 'approved_for_payroll')
                ->latest()
                ->limit(50)
                ->get();
        }

        return view('ess.tax-simulations', [
            'active' => 'ess-tax-simulations',
            'employee' => $employee,
            'profile' => $profile,
            'simulations' => $simulations,
            'summary' => [
                'total' => $simulations->count(),
                'pph21' => (float) $simulations->sum('pph21_amount'),
                'thp' => (float) $simulations->sum('take_home_pay'),
            ],
        ]);
    }
}
