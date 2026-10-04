<?php

namespace App\Http\Controllers;

use App\Models\TaxCalculationAudit;
use App\Models\TaxProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaxCalculationAuditController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $audits = TaxCalculationAudit::with(['taxProfile', 'company', 'branch', 'creator'])
            ->latest()
            ->limit(100)
            ->get();

        return view('payroll.tax-calculation-audits', [
            'active' => 'tax-audits',
            'profiles' => TaxProfile::with(['company', 'branch'])->where('is_active', true)->orderBy('employee_name')->get(),
            'audits' => $audits,
            'summary' => [
                'total' => TaxCalculationAudit::count(),
                'final' => TaxCalculationAudit::where('calculation_status', 'final')->count(),
                'draft' => TaxCalculationAudit::where('calculation_status', 'draft')->count(),
                'total_pph21' => TaxCalculationAudit::sum('pph21_amount'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'tax_profile_id' => ['required', 'exists:tax_profiles,id'],
            'period' => ['required', 'date_format:Y-m'],
            'gross_income' => ['required', 'numeric', 'min:0'],
            'taxable_allowance' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'calculation_status' => ['required', 'in:draft,final'],
        ]);

        $profile = TaxProfile::with(['company', 'branch'])->findOrFail($data['tax_profile_id']);
        $grossIncome = (float) $data['gross_income'];
        $taxableAllowance = (float) ($data['taxable_allowance'] ?? 0);
        $deduction = (float) ($data['deduction'] ?? 0);
        $netIncome = max(0, $grossIncome + $taxableAllowance - $deduction);
        $annualizedIncome = $netIncome * 12;
        $ptkpAmount = $this->ptkpAmount($profile->ptkp_status);
        $pkpAmount = max(0, floor(($annualizedIncome - $ptkpAmount) / 1000) * 1000);
        $annualTax = $this->progressiveTax($pkpAmount);
        $monthlyTax = round($annualTax / 12, 2);

        if ($profile->tax_method === 'gross_up' && $monthlyTax > 0) {
            $monthlyTax = round($monthlyTax / 0.95, 2);
        }

        $steps = [
            ['label' => 'Penghasilan bruto', 'formula' => 'Gaji bruto + tunjangan kena pajak', 'amount' => $grossIncome + $taxableAllowance],
            ['label' => 'Pengurang', 'formula' => 'Potongan payroll yang diakui', 'amount' => $deduction],
            ['label' => 'Net income bulanan', 'formula' => 'Bruto - pengurang', 'amount' => $netIncome],
            ['label' => 'Annualized income', 'formula' => 'Net income bulanan x 12', 'amount' => $annualizedIncome],
            ['label' => 'PTKP', 'formula' => "Status {$profile->ptkp_status}", 'amount' => $ptkpAmount],
            ['label' => 'PKP', 'formula' => 'Annualized income - PTKP, dibulatkan ribuan ke bawah', 'amount' => $pkpAmount],
            ['label' => 'PPh 21 bulanan', 'formula' => 'Tarif progresif / 12, disesuaikan metode pajak', 'amount' => $monthlyTax],
        ];

        $audit = TaxCalculationAudit::create([
            'tax_profile_id' => $profile->id,
            'company_id' => $profile->company_id,
            'branch_id' => $profile->branch_id,
            'period' => $data['period'],
            'gross_income' => $grossIncome,
            'taxable_allowance' => $taxableAllowance,
            'deduction' => $deduction,
            'net_income' => $netIncome,
            'annualized_income' => $annualizedIncome,
            'ptkp_amount' => $ptkpAmount,
            'pkp_amount' => $pkpAmount,
            'pph21_amount' => $monthlyTax,
            'tax_method' => $profile->tax_method,
            'calculation_status' => $data['calculation_status'],
            'calculation_steps' => $steps,
            'source_snapshot' => [
                'employee_name' => $profile->employee_name,
                'employee_number' => $profile->employee_number,
                'nik' => $profile->nik,
                'npwp' => $profile->npwp,
                'ptkp_status' => $profile->ptkp_status,
                'tax_method' => $profile->tax_method,
                'company_code' => $profile->company?->code,
                'branch_code' => $profile->branch?->code,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'tax_calculation.explained',
            "Riwayat perhitungan PPh 21 {$profile->employee_name} periode {$audit->period} dibuat.",
            $audit,
            [],
            $audit->only(['tax_profile_id', 'period', 'pph21_amount', 'calculation_status']),
            $request
        );

        return redirect()->route('payroll.tax-audits')->with('success', 'Riwayat perhitungan PPh 21 berhasil disimpan.');
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
