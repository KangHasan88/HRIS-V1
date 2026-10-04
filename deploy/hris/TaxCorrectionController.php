<?php

namespace App\Http\Controllers;

use App\Models\TaxCorrection;
use App\Models\TaxMonthlyReportPackage;
use App\Models\TaxProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TaxCorrectionController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-corrections', [
            'active' => 'tax-corrections',
            'profiles' => TaxProfile::where('is_active', true)->orderBy('employee_name')->get(),
            'packages' => TaxMonthlyReportPackage::with('company')->latest()->limit(50)->get(),
            'corrections' => TaxCorrection::with(['taxProfile', 'package.company', 'company'])->latest()->limit(100)->get(),
            'summary' => [
                'total' => TaxCorrection::count(),
                'approved' => TaxCorrection::where('status', 'approved')->count(),
                'pending' => TaxCorrection::whereIn('status', ['draft', 'submitted', 'review'])->count(),
                'delta' => TaxCorrection::sum('delta_pph21_amount'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'tax_profile_id' => ['required', 'exists:tax_profiles,id'],
            'tax_monthly_report_package_id' => ['nullable', 'exists:tax_monthly_report_packages,id'],
            'correction_type' => ['required', 'in:pembetulan_pelaporan,koreksi_payroll,retroaktif,refund,kurang_bayar'],
            'period' => ['required', 'date_format:Y-m'],
            'payroll_period' => ['nullable', 'date_format:Y-m'],
            'previous_gross_income' => ['required', 'numeric', 'min:0'],
            'corrected_gross_income' => ['required', 'numeric', 'min:0'],
            'previous_tax_base' => ['required', 'numeric', 'min:0'],
            'corrected_tax_base' => ['required', 'numeric', 'min:0'],
            'previous_pph21_amount' => ['required', 'numeric', 'min:0'],
            'corrected_pph21_amount' => ['required', 'numeric', 'min:0'],
            'payroll_adjustment_type' => ['required', 'in:none,next_payroll_refund,next_payroll_deduction,manual_transfer,manual_journal'],
            'status' => ['required', 'in:draft,submitted,review,approved,rejected,applied'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $profile = TaxProfile::with('company')->findOrFail($data['tax_profile_id']);
        $package = isset($data['tax_monthly_report_package_id'])
            ? TaxMonthlyReportPackage::find($data['tax_monthly_report_package_id'])
            : null;
        $delta = (float) $data['corrected_pph21_amount'] - (float) $data['previous_pph21_amount'];

        $correction = TaxCorrection::create([
            ...$data,
            'company_id' => $profile->company_id,
            'correction_number' => $this->correctionNumber($data['period']),
            'delta_pph21_amount' => $delta,
            'before_snapshot' => [
                'employee_number' => $profile->employee_number,
                'employee_name' => $profile->employee_name,
                'period' => $data['period'],
                'gross_income' => (float) $data['previous_gross_income'],
                'tax_base' => (float) $data['previous_tax_base'],
                'pph21_amount' => (float) $data['previous_pph21_amount'],
                'package_number' => $package?->package_number,
            ],
            'after_snapshot' => [
                'employee_number' => $profile->employee_number,
                'employee_name' => $profile->employee_name,
                'period' => $data['period'],
                'payroll_period' => $data['payroll_period'] ?? null,
                'gross_income' => (float) $data['corrected_gross_income'],
                'tax_base' => (float) $data['corrected_tax_base'],
                'pph21_amount' => (float) $data['corrected_pph21_amount'],
                'delta_pph21_amount' => $delta,
                'payroll_adjustment_type' => $data['payroll_adjustment_type'],
            ],
            'workflow_snapshot' => [
                'status' => $data['status'],
                'requires_approval' => in_array($data['status'], ['submitted', 'review'], true),
                'impact' => $delta > 0 ? 'kurang_bayar' : ($delta < 0 ? 'lebih_bayar/refund' : 'tidak_ada_selisih'),
                'created_from' => 'payroll_tax_correction',
            ],
            'approved_at' => in_array($data['status'], ['approved', 'applied'], true) ? now() : null,
            'created_by' => auth()->id(),
            'approved_by' => in_array($data['status'], ['approved', 'applied'], true) ? auth()->id() : null,
        ]);

        AuditLogger::record(
            'tax_correction.created',
            "Koreksi pajak {$correction->correction_number} dibuat untuk {$profile->employee_name}.",
            $correction,
            [],
            $correction->only(['correction_number', 'period', 'correction_type', 'delta_pph21_amount', 'status']),
            $request
        );

        return redirect()->route('payroll.tax-corrections')->with('success', 'Koreksi pajak berhasil dibuat.');
    }

    private function correctionNumber(string $period): string
    {
        return 'TAXCOR-' . str_replace('-', '', $period) . '-' . Str::upper(Str::random(6));
    }
}
