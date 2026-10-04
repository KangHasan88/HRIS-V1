<?php

namespace App\Http\Controllers;

use App\Models\TaxIrregularIncome;
use App\Models\TaxProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaxIrregularIncomeController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-irregular-incomes', [
            'active' => 'tax-irregular',
            'profiles' => TaxProfile::with(['company', 'branch'])->where('is_active', true)->orderBy('employee_name')->get(),
            'items' => TaxIrregularIncome::with('taxProfile')->latest()->limit(100)->get(),
            'incomeTypes' => [
                'thr' => 'THR',
                'bonus' => 'Bonus',
                'commission' => 'Komisi',
                'irregular_allowance' => 'Tunjangan Tidak Teratur',
                'final_tax' => 'Pajak Final',
            ],
            'taxMethods' => [
                'gross' => 'Gross',
                'gross_up' => 'Gross Up',
                'net' => 'Net',
                'final' => 'Final',
            ],
            'summary' => [
                'total' => TaxIrregularIncome::count(),
                'final' => TaxIrregularIncome::where('calculation_status', 'final')->count(),
                'total_tax' => TaxIrregularIncome::sum('pph21_amount'),
                'total_gross' => TaxIrregularIncome::sum('gross_amount'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'tax_profile_id' => ['required', 'exists:tax_profiles,id'],
            'period' => ['required', 'date_format:Y-m'],
            'income_type' => ['required', 'in:thr,bonus,commission,irregular_allowance,final_tax'],
            'tax_method' => ['required', 'in:gross,gross_up,net,final'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'calculation_status' => ['required', 'in:draft,final'],
        ]);

        $profile = TaxProfile::with(['company', 'branch'])->findOrFail($data['tax_profile_id']);
        $gross = (float) $data['gross_amount'];
        $rate = (float) $data['tax_rate'];
        $taxable = $gross;
        $tax = round($taxable * $rate, 2);

        if ($data['tax_method'] === 'gross_up' && $rate > 0 && $rate < 1) {
            $tax = round(($taxable / (1 - $rate)) - $taxable, 2);
        }

        $net = match ($data['tax_method']) {
            'net' => $gross,
            'gross_up' => $gross,
            default => max(0, $gross - $tax),
        };

        $steps = [
            ['label' => 'Jenis penghasilan', 'formula' => str_replace('_', ' ', $data['income_type']), 'amount' => 0],
            ['label' => 'Bruto tidak teratur', 'formula' => 'Nilai THR/bonus/komisi/penghasilan final', 'amount' => $gross],
            ['label' => 'Dasar pajak', 'formula' => 'Bruto kena pajak sesuai tipe penghasilan', 'amount' => $taxable],
            ['label' => 'Tarif', 'formula' => strtoupper($data['tax_method']) . ' x rate input', 'amount' => $rate],
            ['label' => 'PPh 21', 'formula' => $data['tax_method'] === 'gross_up' ? 'Gross-up tax allowance' : 'Dasar pajak x tarif', 'amount' => $tax],
            ['label' => 'Net diterima', 'formula' => 'Bruto - pajak dipotong, kecuali metode net/gross-up', 'amount' => $net],
        ];

        $item = TaxIrregularIncome::create([
            'tax_profile_id' => $profile->id,
            'company_id' => $profile->company_id,
            'branch_id' => $profile->branch_id,
            'period' => $data['period'],
            'income_type' => $data['income_type'],
            'tax_method' => $data['tax_method'],
            'gross_amount' => $gross,
            'taxable_amount' => $taxable,
            'tax_rate' => $rate,
            'pph21_amount' => $tax,
            'net_amount' => $net,
            'calculation_status' => $data['calculation_status'],
            'calculation_steps' => $steps,
            'source_snapshot' => [
                'employee_name' => $profile->employee_name,
                'employee_number' => $profile->employee_number,
                'ptkp_status' => $profile->ptkp_status,
                'tax_method_profile' => $profile->tax_method,
                'company_code' => $profile->company?->code,
                'branch_code' => $profile->branch?->code,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'tax_irregular_income.calculated',
            "Pajak {$item->income_type} {$profile->employee_name} periode {$item->period} dihitung.",
            $item,
            [],
            $item->only(['tax_profile_id', 'period', 'income_type', 'tax_method', 'gross_amount', 'pph21_amount']),
            $request
        );

        return redirect()->route('payroll.tax-irregular')->with('success', 'Pajak penghasilan tidak teratur berhasil dihitung.');
    }
}
