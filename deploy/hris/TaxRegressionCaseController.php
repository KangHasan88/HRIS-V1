<?php

namespace App\Http\Controllers;

use App\Models\TaxRegressionCase;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TaxRegressionCaseController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-regression-cases', [
            'active' => 'tax-regression-cases',
            'cases' => TaxRegressionCase::latest()->limit(120)->get(),
            'summary' => [
                'total' => TaxRegressionCase::count(),
                'passed' => TaxRegressionCase::where('run_status', 'passed')->count(),
                'failed' => TaxRegressionCase::where('run_status', 'failed')->count(),
                'coretax_ready' => TaxRegressionCase::where('coretax_validation_status', 'valid')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'case_type' => ['required', 'in:pph21_monthly,pph21_annual,irregular_income,non_permanent_worker,coretax_template'],
            'case_name' => ['required', 'string', 'max:160'],
            'period' => ['required', 'date_format:Y-m'],
            'gross_income' => ['required', 'numeric', 'min:0'],
            'taxable_allowance' => ['required', 'numeric', 'min:0'],
            'deduction' => ['required', 'numeric', 'min:0'],
            'ter_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'expected_pph21_amount' => ['required', 'numeric', 'min:0'],
            'coretax_schema_version' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $taxBase = max(0, (float) $data['gross_income'] + (float) $data['taxable_allowance'] - (float) $data['deduction']);
        $actualPph21 = round($taxBase * (float) $data['ter_rate'], 2);
        $variance = $actualPph21 - (float) $data['expected_pph21_amount'];
        $passed = abs($variance) < 0.01;
        $coretaxStatus = ($data['coretax_schema_version'] ?? null)
            ? ($passed ? 'valid' : 'needs_review')
            : 'not_required';

        $case = TaxRegressionCase::create([
            'case_number' => $this->caseNumber($data['period']),
            'case_type' => $data['case_type'],
            'case_name' => $data['case_name'],
            'period' => $data['period'],
            'input_payload' => [
                'gross_income' => (float) $data['gross_income'],
                'taxable_allowance' => (float) $data['taxable_allowance'],
                'deduction' => (float) $data['deduction'],
                'ter_rate' => (float) $data['ter_rate'],
                'tax_base' => $taxBase,
            ],
            'expected_payload' => [
                'pph21_amount' => (float) $data['expected_pph21_amount'],
                'coretax_schema_version' => $data['coretax_schema_version'] ?? null,
            ],
            'actual_payload' => [
                'tax_base' => $taxBase,
                'pph21_amount' => $actualPph21,
                'coretax_columns_checked' => (bool) ($data['coretax_schema_version'] ?? null),
            ],
            'expected_pph21_amount' => $data['expected_pph21_amount'],
            'actual_pph21_amount' => $actualPph21,
            'variance_amount' => $variance,
            'coretax_schema_version' => $data['coretax_schema_version'] ?? null,
            'coretax_validation_status' => $coretaxStatus,
            'run_status' => $passed ? 'passed' : 'failed',
            'assertion_snapshot' => [
                ['name' => 'PPh 21 expected vs actual', 'passed' => $passed, 'variance' => $variance],
                ['name' => 'Coretax schema readiness', 'passed' => $coretaxStatus !== 'needs_review', 'schema' => $data['coretax_schema_version'] ?? null],
            ],
            'last_run_at' => now(),
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'tax_regression_case.executed',
            "Regression case {$case->case_number} dijalankan dengan status {$case->run_status}.",
            $case,
            [],
            $case->only(['case_number', 'case_type', 'run_status', 'variance_amount', 'coretax_validation_status']),
            $request
        );

        return redirect()->route('payroll.tax-regression-cases')->with('success', 'Regression case berhasil dijalankan.');
    }

    private function caseNumber(string $period): string
    {
        return 'TAXREG-' . str_replace('-', '', $period) . '-' . Str::upper(Str::random(6));
    }
}
