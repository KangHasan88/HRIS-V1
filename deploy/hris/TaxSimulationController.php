<?php

namespace App\Http\Controllers;

use App\Models\TaxMonthlyCalculation;
use App\Models\TaxProfile;
use App\Models\TaxSimulation;
use App\Models\TaxTerRate;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxSimulationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-simulations', [
            'active' => 'tax-simulations',
            'profiles' => TaxProfile::where('is_active', true)->orderBy('employee_name')->get(),
            'simulations' => TaxSimulation::with(['taxProfile', 'company'])->latest()->limit(100)->get(),
            'summary' => [
                'total' => TaxSimulation::count(),
                'review' => TaxSimulation::whereIn('scenario_status', ['ready_for_review', 'approved_for_payroll'])->count(),
                'avg_pph21' => TaxSimulation::avg('pph21_amount') ?: 0,
                'company_borne' => TaxSimulation::sum('company_tax_borne_amount'),
                'thr_bonus' => TaxSimulation::whereIn('scenario_type', ['thr', 'bonus'])->count(),
                'visible' => TaxSimulation::where('visible_to_employee', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'tax_profile_id' => ['required', 'exists:tax_profiles,id'],
            'scenario_name' => ['required', 'string', 'max:120'],
            'scenario_type' => ['required', Rule::in(['monthly', 'thr', 'bonus', 'final_tax', 'gross_up'])],
            'period' => ['required', 'date_format:Y-m'],
            'tax_method' => ['required', Rule::in(['gross', 'gross_up', 'net', 'company_borne', 'tax_allowance'])],
            'gross_income' => ['required', 'numeric', 'min:0'],
            'taxable_allowance' => ['required', 'numeric', 'min:0'],
            'irregular_income' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['required', 'numeric', 'min:0'],
            'final_tax_amount' => ['nullable', 'numeric', 'min:0'],
            'scenario_status' => ['required', Rule::in(['draft', 'ready_for_review', 'approved_for_payroll', 'rejected'])],
            'visible_to_employee' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $profile = TaxProfile::with('company')->findOrFail($data['tax_profile_id']);
        $grossIncome = (float) $data['gross_income'];
        $taxableAllowance = (float) $data['taxable_allowance'];
        $irregularIncome = (float) ($data['irregular_income'] ?? 0);
        $deduction = (float) $data['deduction'];
        $finalTax = (float) ($data['final_tax_amount'] ?? 0);
        $category = $this->terCategory($profile->ptkp_status);
        $regularBase = max(0, $grossIncome + $taxableAllowance - $deduction);
        $taxBase = max(0, $regularBase + $irregularIncome);
        $rate = $this->findTerRate($category, $taxBase);
        $terRate = $rate ? (float) $rate->rate : 0.005;
        $grossUpIterations = 0;
        $taxAllowance = 0.0;
        $companyBorne = 0.0;

        if (in_array($data['tax_method'], ['gross_up', 'net'], true)) {
            [$taxBase, $terRate, $taxAllowance, $grossUpIterations] = $this->grossUp($category, $regularBase, $irregularIncome, $data['tax_method'] === 'net');
        } elseif ($data['tax_method'] === 'tax_allowance') {
            $taxAllowance = round($taxBase * $terRate, 2);
            $taxBase = max(0, $taxBase + $taxAllowance);
            $rate = $this->findTerRate($category, $taxBase);
            $terRate = $rate ? (float) $rate->rate : $terRate;
        }

        $pph21 = round($taxBase * $terRate, 2);
        if ($data['tax_method'] === 'company_borne') {
            $companyBorne = $pph21;
        }

        $takeHomePay = match ($data['tax_method']) {
            'company_borne', 'net' => max(0, $taxBase),
            default => max(0, $taxBase - $pph21 - $finalTax),
        };

        $latestFinal = TaxMonthlyCalculation::where('tax_profile_id', $profile->id)
            ->where('period', $data['period'])
            ->where('calculation_status', 'final')
            ->latest()
            ->first();

        $simulation = TaxSimulation::create([
            ...$data,
            'company_id' => $profile->company_id,
            'simulation_number' => $this->simulationNumber($data['period']),
            'irregular_income' => $irregularIncome,
            'tax_base' => $taxBase,
            'ter_rate' => $terRate,
            'pph21_amount' => $pph21,
            'tax_allowance_amount' => $taxAllowance,
            'company_tax_borne_amount' => $companyBorne,
            'final_tax_amount' => $finalTax,
            'gross_up_iterations' => $grossUpIterations,
            'take_home_pay' => $takeHomePay,
            'visible_to_employee' => $request->boolean('visible_to_employee'),
            'approved_at' => $data['scenario_status'] === 'approved_for_payroll' ? now() : null,
            'approved_by' => $data['scenario_status'] === 'approved_for_payroll' ? auth()->user()?->name : null,
            'input_snapshot' => [
                'employee_number' => $profile->employee_number,
                'employee_name' => $profile->employee_name,
                'ptkp_status' => $profile->ptkp_status,
                'scenario_type' => $data['scenario_type'],
                'tax_method' => $data['tax_method'],
                'gross_income' => $grossIncome,
                'taxable_allowance' => $taxableAllowance,
                'irregular_income' => $irregularIncome,
                'deduction' => $deduction,
                'final_tax_amount' => $finalTax,
            ],
            'result_snapshot' => [
                'ter_category' => $category,
                'ter_rate' => $terRate,
                'regular_base' => $regularBase,
                'tax_base' => $taxBase,
                'pph21_amount' => $pph21,
                'tax_allowance_amount' => $taxAllowance,
                'company_tax_borne_amount' => $companyBorne,
                'final_tax_amount' => $finalTax,
                'gross_up_iterations' => $grossUpIterations,
                'take_home_pay' => $takeHomePay,
            ],
            'comparison_snapshot' => [
                'baseline_pph21' => $latestFinal ? (float) $latestFinal->pph21_amount : null,
                'baseline_take_home_pay' => $latestFinal ? (float) $latestFinal->take_home_pay : null,
                'delta_pph21' => $latestFinal ? $pph21 - (float) $latestFinal->pph21_amount : null,
                'delta_take_home_pay' => $latestFinal ? $takeHomePay - (float) $latestFinal->take_home_pay : null,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'tax_simulation.created',
            "Simulasi pajak {$simulation->simulation_number} dibuat untuk {$profile->employee_name}.",
            $simulation,
            [],
            $simulation->only(['simulation_number', 'scenario_type', 'period', 'tax_method', 'pph21_amount', 'scenario_status']),
            $request
        );

        return redirect()->route('payroll.tax-simulations')->with('success', 'Simulasi pajak berhasil dibuat.');
    }

    private function grossUp(string $category, float $regularBase, float $irregularIncome, bool $netMethod): array
    {
        $taxBase = max(0, $regularBase + $irregularIncome);
        $allowance = 0.0;
        $rate = 0.005;
        $iterations = 0;

        for ($i = 1; $i <= 5; $i++) {
            $rateModel = $this->findTerRate($category, $taxBase);
            $rate = $rateModel ? (float) $rateModel->rate : $rate;
            $estimatedTax = round($taxBase * $rate, 2);
            $newAllowance = $netMethod ? round($estimatedTax / max(0.000001, 1 - $rate), 2) : $estimatedTax;
            $nextBase = max(0, $regularBase + $irregularIncome + $newAllowance);
            $iterations = $i;

            if (abs($nextBase - $taxBase) < 1) {
                $allowance = $newAllowance;
                $taxBase = $nextBase;
                break;
            }

            $allowance = $newAllowance;
            $taxBase = $nextBase;
        }

        $rateModel = $this->findTerRate($category, $taxBase);

        return [$taxBase, $rateModel ? (float) $rateModel->rate : $rate, $allowance, $iterations];
    }

    private function findTerRate(string $category, float $taxBase): ?TaxTerRate
    {
        return TaxTerRate::where('category', $category)
            ->where('income_from', '<=', $taxBase)
            ->where(function ($query) use ($taxBase) {
                $query->whereNull('income_to')->orWhere('income_to', '>=', $taxBase);
            })
            ->orderByDesc('income_from')
            ->first();
    }

    private function terCategory(string $ptkpStatus): string
    {
        return str_starts_with($ptkpStatus, 'K/') ? 'B' : 'A';
    }

    private function simulationNumber(string $period): string
    {
        return 'TAXSIM-' . str_replace('-', '', $period) . '-' . Str::upper(Str::random(6));
    }
}
