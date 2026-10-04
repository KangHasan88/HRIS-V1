<?php

namespace App\Http\Controllers;

use App\Models\TaxMonthlyCalculation;
use App\Models\PayrollTaxComponent;
use App\Models\TaxProfile;
use App\Models\TaxTerRate;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaxMonthlyCalculationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-monthly', [
            'active' => 'tax-monthly',
            'profiles' => TaxProfile::with(['company', 'branch'])->where('is_active', true)->orderBy('employee_name')->get(),
            'components' => PayrollTaxComponent::with('company')->where('is_active', true)->orderBy('sort_order')->orderBy('component_code')->get(),
            'rates' => TaxTerRate::where('is_active', true)->orderBy('category')->orderBy('income_from')->get(),
            'calculations' => TaxMonthlyCalculation::with(['taxProfile', 'terRate'])->latest()->limit(100)->get(),
            'summary' => [
                'rates' => TaxTerRate::where('is_active', true)->count(),
                'calculations' => TaxMonthlyCalculation::count(),
                'final' => TaxMonthlyCalculation::where('calculation_status', 'final')->count(),
                'total_pph21' => TaxMonthlyCalculation::sum('pph21_amount'),
            ],
        ]);
    }

    public function storeComponent(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'component_code' => ['required', 'string', 'max:50'],
            'component_name' => ['required', 'string', 'max:255'],
            'component_type' => ['required', 'in:earning,deduction,benefit,employer_contribution'],
            'tax_treatment' => ['required', 'in:taxable_income,non_taxable,deduction,company_cost'],
            'payroll_formula_key' => ['nullable', 'string', 'max:100'],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'is_taxable' => ['nullable', 'boolean'],
            'is_recurring' => ['nullable', 'boolean'],
            'dependencies' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $component = PayrollTaxComponent::create([
            ...$data,
            'component_code' => strtoupper($data['component_code']),
            'default_amount' => $data['default_amount'] ?? 0,
            'is_taxable' => $request->boolean('is_taxable'),
            'is_recurring' => $request->boolean('is_recurring', true),
            'dependencies' => collect(explode(',', (string) ($data['dependencies'] ?? '')))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values()
                ->all(),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        AuditLogger::record(
            'payroll_tax_component.created',
            "Komponen pajak payroll {$component->component_code} dibuat.",
            $component,
            [],
            $component->toArray(),
            $request
        );

        return redirect()->route('payroll.tax-monthly')->with('success', 'Komponen pajak payroll berhasil ditambahkan.');
    }

    public function storeRate(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'category' => ['required', 'in:A,B,C'],
            'income_from' => ['required', 'numeric', 'min:0'],
            'income_to' => ['nullable', 'numeric', 'gte:income_from'],
            'rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'source_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $rate = TaxTerRate::create([...$data, 'is_active' => true]);

        AuditLogger::record(
            'tax_ter_rate.created',
            "Tarif TER kategori {$rate->category} dibuat.",
            $rate,
            [],
            $rate->toArray(),
            $request
        );

        return redirect()->route('payroll.tax-monthly')->with('success', 'Tarif TER berhasil ditambahkan.');
    }

    public function calculate(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'tax_profile_id' => ['required', 'exists:tax_profiles,id'],
            'period' => ['required', 'date_format:Y-m'],
            'gross_income' => ['required', 'numeric', 'min:0'],
            'taxable_allowance' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'tax_method' => ['required', 'in:gross,gross_up,net,company_borne,tax_allowance'],
            'component_ids' => ['nullable', 'array'],
            'component_ids.*' => ['integer', 'exists:payroll_tax_components,id'],
            'calculation_status' => ['required', 'in:draft,final'],
        ]);

        $profile = TaxProfile::with(['company', 'branch'])->findOrFail($data['tax_profile_id']);
        $components = PayrollTaxComponent::where('is_active', true)
            ->whereIn('id', $data['component_ids'] ?? [])
            ->orderBy('sort_order')
            ->get();
        $componentTaxableIncome = (float) $components->where('tax_treatment', 'taxable_income')->sum('default_amount');
        $componentDeduction = (float) $components->where('tax_treatment', 'deduction')->sum('default_amount');
        $componentCompanyCost = (float) $components->where('tax_treatment', 'company_cost')->sum('default_amount');
        $grossIncome = (float) $data['gross_income'];
        $taxableAllowance = (float) ($data['taxable_allowance'] ?? 0);
        $deduction = (float) ($data['deduction'] ?? 0);
        $taxMethod = $data['tax_method'];
        $baseAmount = max(0, $grossIncome + $taxableAllowance + $componentTaxableIncome - $deduction - $componentDeduction);
        $category = $this->terCategory($profile->ptkp_status);
        $terRate = $this->findTerRate($category, $baseAmount);
        $rate = (float) $terRate->rate;
        $taxAllowanceAmount = 0.0;
        $companyTaxBorneAmount = 0.0;
        $takeHomePay = $baseAmount;

        if ($taxMethod === 'gross_up') {
            $taxAllowanceAmount = $rate >= 1 ? 0 : round(($baseAmount * $rate) / max(0.000001, 1 - $rate), 2);
            $baseAmount = max(0, $baseAmount + $taxAllowanceAmount);
            $terRate = $this->findTerRate($category, $baseAmount);
            $rate = (float) $terRate->rate;
        } elseif ($taxMethod === 'net') {
            $baseAmount = $rate >= 1 ? $baseAmount : round($baseAmount / max(0.000001, 1 - $rate), 2);
            $terRate = $this->findTerRate($category, $baseAmount);
            $rate = (float) $terRate->rate;
        } elseif ($taxMethod === 'tax_allowance') {
            $taxAllowanceAmount = round($baseAmount * $rate, 2);
            $baseAmount = max(0, $baseAmount + $taxAllowanceAmount);
            $terRate = $this->findTerRate($category, $baseAmount);
            $rate = (float) $terRate->rate;
        }

        $pph21 = round($baseAmount * $rate, 2);
        if ($taxMethod === 'company_borne') {
            $companyTaxBorneAmount = $pph21;
            $takeHomePay = $baseAmount;
        } elseif ($taxMethod === 'gross_up') {
            $takeHomePay = max(0, $baseAmount - $pph21);
        } elseif ($taxMethod === 'net') {
            $takeHomePay = max(0, $grossIncome + $taxableAllowance + $componentTaxableIncome - $deduction - $componentDeduction);
        } else {
            $takeHomePay = max(0, $baseAmount - $pph21);
        }

        $steps = [
            ['label' => 'Kategori TER', 'formula' => "Status PTKP {$profile->ptkp_status} dipetakan ke kategori {$category}", 'amount' => 0],
            ['label' => 'Metode pajak', 'formula' => $this->taxMethodLabels()[$taxMethod], 'amount' => 0],
            ['label' => 'Komponen taxable', 'formula' => 'Total komponen payroll dengan tax treatment taxable income', 'amount' => $componentTaxableIncome],
            ['label' => 'Komponen pengurang', 'formula' => 'Total komponen payroll dengan tax treatment deduction', 'amount' => $componentDeduction],
            ['label' => 'Company cost', 'formula' => 'Komponen biaya perusahaan yang tidak memotong THP', 'amount' => $componentCompanyCost],
            ['label' => 'Dasar TER', 'formula' => 'Gaji bruto + tunjangan kena pajak + komponen taxable + tunjangan pajak - pengurang - komponen pengurang', 'amount' => $baseAmount],
            ['label' => 'Tarif TER', 'formula' => "Bracket {$terRate->income_from} sampai " . ($terRate->income_to ?: 'tak terbatas'), 'amount' => (float) $terRate->rate],
            ['label' => 'Tunjangan pajak', 'formula' => 'Nilai tambahan pajak untuk gross-up atau tax allowance', 'amount' => $taxAllowanceAmount],
            ['label' => 'Pajak ditanggung perusahaan', 'formula' => 'Nilai pajak yang tidak memotong take-home pay', 'amount' => $companyTaxBorneAmount],
            ['label' => 'PPh 21 bulanan', 'formula' => 'Dasar TER x Tarif TER', 'amount' => $pph21],
            ['label' => 'Take-home pay', 'formula' => 'Dasar pembayaran setelah perlakuan metode pajak', 'amount' => $takeHomePay],
        ];

        $calculation = TaxMonthlyCalculation::create([
            'tax_profile_id' => $profile->id,
            'tax_ter_rate_id' => $terRate->id,
            'company_id' => $profile->company_id,
            'branch_id' => $profile->branch_id,
            'period' => $data['period'],
            'ter_category' => $category,
            'tax_method' => $taxMethod,
            'gross_income' => $grossIncome,
            'taxable_allowance' => $taxableAllowance,
            'deduction' => $deduction,
            'ter_base_amount' => $baseAmount,
            'ter_rate' => $terRate->rate,
            'pph21_amount' => $pph21,
            'tax_allowance_amount' => $taxAllowanceAmount,
            'company_tax_borne_amount' => $companyTaxBorneAmount,
            'take_home_pay' => $takeHomePay,
            'calculation_status' => $data['calculation_status'],
            'calculation_steps' => $steps,
            'source_snapshot' => [
                'employee_name' => $profile->employee_name,
                'employee_number' => $profile->employee_number,
                'ptkp_status' => $profile->ptkp_status,
                'profile_tax_method' => $profile->tax_method,
                'tax_method' => $taxMethod,
                'payroll_components' => $components->map(fn ($component) => [
                    'code' => $component->component_code,
                    'name' => $component->component_name,
                    'type' => $component->component_type,
                    'tax_treatment' => $component->tax_treatment,
                    'default_amount' => (float) $component->default_amount,
                    'formula_key' => $component->payroll_formula_key,
                    'dependencies' => $component->dependencies ?? [],
                ])->values()->all(),
                'component_taxable_income' => $componentTaxableIncome,
                'component_deduction' => $componentDeduction,
                'component_company_cost' => $componentCompanyCost,
                'company_code' => $profile->company?->code,
                'branch_code' => $profile->branch?->code,
                'ter_source_reference' => $terRate->source_reference,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'tax_monthly.calculated',
            "PPh 21 bulanan {$profile->employee_name} periode {$calculation->period} dihitung.",
            $calculation,
            [],
            $calculation->only(['tax_profile_id', 'period', 'ter_category', 'tax_method', 'ter_rate', 'pph21_amount', 'take_home_pay']),
            $request
        );

        return redirect()->route('payroll.tax-monthly')->with('success', 'PPh 21 bulanan berhasil dihitung.');
    }

    private function terCategory(string $ptkpStatus): string
    {
        return match ($ptkpStatus) {
            'TK/2', 'TK/3', 'K/1', 'K/2' => 'B',
            'K/3' => 'C',
            default => 'A',
        };
    }

    private function findTerRate(string $category, float $baseAmount): TaxTerRate
    {
        return TaxTerRate::where('is_active', true)
            ->where('category', $category)
            ->where('income_from', '<=', $baseAmount)
            ->where(fn ($query) => $query->whereNull('income_to')->orWhere('income_to', '>=', $baseAmount))
            ->orderByDesc('income_from')
            ->firstOrFail();
    }

    private function taxMethodLabels(): array
    {
        return [
            'gross' => 'Gross - PPh 21 dipotong dari penghasilan karyawan.',
            'gross_up' => 'Gross-up - perusahaan memberi tunjangan pajak agar take-home pay mendekati nilai awal.',
            'net' => 'Net - input diperlakukan sebagai target take-home pay.',
            'company_borne' => 'Pajak ditanggung perusahaan - PPh 21 tidak mengurangi take-home pay.',
            'tax_allowance' => 'Tunjangan pajak - nilai pajak awal ditambahkan sebagai tunjangan kena pajak.',
        ];
    }
}
