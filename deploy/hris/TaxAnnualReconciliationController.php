<?php

namespace App\Http\Controllers;

use App\Models\TaxAnnualReconciliation;
use App\Models\TaxMonthlyCalculation;
use App\Models\TaxProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaxAnnualReconciliationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-annual-reconciliations', [
            'active' => 'tax-annual',
            'profiles' => TaxProfile::with(['company', 'branch'])->where('is_active', true)->orderBy('employee_name')->get(),
            'reconciliations' => TaxAnnualReconciliation::with('taxProfile')->latest()->limit(100)->get(),
            'summary' => [
                'total' => TaxAnnualReconciliation::count(),
                'underpaid' => TaxAnnualReconciliation::where('true_up_status', 'underpaid')->count(),
                'overpaid' => TaxAnnualReconciliation::where('true_up_status', 'overpaid')->count(),
                'net_true_up' => TaxAnnualReconciliation::sum('true_up_amount'),
            ],
        ]);
    }

    public function reconcile(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'tax_profile_id' => ['required', 'exists:tax_profiles,id'],
            'tax_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'calculation_status' => ['required', 'in:draft,final'],
        ]);

        $profile = TaxProfile::with(['company', 'branch'])->findOrFail($data['tax_profile_id']);
        $monthlyRows = TaxMonthlyCalculation::where('tax_profile_id', $profile->id)
            ->where('period', 'like', $data['tax_year'] . '-%')
            ->where('calculation_status', 'final')
            ->orderBy('period')
            ->get();

        $gross = (float) $monthlyRows->sum('gross_income');
        $allowance = (float) $monthlyRows->sum('taxable_allowance');
        $deduction = (float) $monthlyRows->sum('deduction');
        $net = max(0, $gross + $allowance - $deduction);
        $ptkp = $this->ptkpAmount($profile->ptkp_status);
        $pkp = max(0, floor(($net - $ptkp) / 1000) * 1000);
        $annualDue = round($this->progressiveTax($pkp), 2);
        $paid = (float) $monthlyRows->sum('pph21_amount');
        $trueUp = round($annualDue - $paid, 2);
        $status = match (true) {
            $trueUp > 0 => 'underpaid',
            $trueUp < 0 => 'overpaid',
            default => 'balanced',
        };

        $reconciliation = TaxAnnualReconciliation::updateOrCreate(
            ['tax_profile_id' => $profile->id, 'tax_year' => $data['tax_year']],
            [
                'company_id' => $profile->company_id,
                'branch_id' => $profile->branch_id,
                'annual_gross_income' => $gross,
                'annual_taxable_allowance' => $allowance,
                'annual_deduction' => $deduction,
                'annual_net_income' => $net,
                'ptkp_amount' => $ptkp,
                'pkp_amount' => $pkp,
                'annual_pph21_due' => $annualDue,
                'monthly_pph21_paid' => $paid,
                'true_up_amount' => $trueUp,
                'true_up_status' => $status,
                'calculation_status' => $data['calculation_status'],
                'monthly_snapshot' => $monthlyRows->map(fn ($row) => [
                    'period' => $row->period,
                    'gross_income' => (float) $row->gross_income,
                    'taxable_allowance' => (float) $row->taxable_allowance,
                    'deduction' => (float) $row->deduction,
                    'pph21_amount' => (float) $row->pph21_amount,
                ])->values()->all(),
                'calculation_steps' => [
                    ['label' => 'Penghasilan neto tahunan', 'formula' => 'Gross + tunjangan kena pajak - pengurang selama tahun pajak', 'amount' => $net],
                    ['label' => 'PTKP', 'formula' => "Status {$profile->ptkp_status}", 'amount' => $ptkp],
                    ['label' => 'PKP', 'formula' => 'Neto tahunan - PTKP, dibulatkan ribuan ke bawah', 'amount' => $pkp],
                    ['label' => 'PPh 21 terutang tahunan', 'formula' => 'Tarif progresif tahunan', 'amount' => $annualDue],
                    ['label' => 'PPh 21 sudah dipotong', 'formula' => 'Total PPh 21 bulanan final', 'amount' => $paid],
                    ['label' => 'True-up Desember', 'formula' => 'PPh 21 tahunan - PPh 21 sudah dipotong', 'amount' => $trueUp],
                ],
                'created_by' => auth()->id(),
            ]
        );

        AuditLogger::record(
            'tax_annual.reconciled',
            "True-up PPh 21 {$profile->employee_name} tahun {$reconciliation->tax_year} dihitung.",
            $reconciliation,
            [],
            $reconciliation->only(['tax_profile_id', 'tax_year', 'annual_pph21_due', 'monthly_pph21_paid', 'true_up_amount', 'true_up_status']),
            $request
        );

        return redirect()->route('payroll.tax-annual')->with('success', 'True-up PPh 21 tahunan berhasil dihitung.');
    }

    private function ptkpAmount(string $status): float
    {
        return [
            'TK/0' => 54000000,
            'TK/1' => 58500000,
            'TK/2' => 63000000,
            'TK/3' => 67500000,
            'K/0' => 58500000,
            'K/1' => 63000000,
            'K/2' => 67500000,
            'K/3' => 72000000,
        ][$status] ?? 54000000;
    }

    private function progressiveTax(float $pkp): float
    {
        $brackets = [
            [60000000, 0.05],
            [190000000, 0.15],
            [250000000, 0.25],
            [4500000000, 0.30],
            [INF, 0.35],
        ];

        $tax = 0;
        $remaining = $pkp;

        foreach ($brackets as [$limit, $rate]) {
            if ($remaining <= 0) {
                break;
            }

            $taxable = min($remaining, $limit);
            $tax += $taxable * $rate;
            $remaining -= $taxable;
        }

        return $tax;
    }
}
