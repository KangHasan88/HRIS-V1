<?php

namespace App\Http\Controllers;

use App\Models\TaxAnnualReconciliation;
use App\Models\TaxCorrection;
use App\Models\TaxIrregularIncome;
use App\Models\TaxMonthlyCalculation;
use App\Models\TaxMonthlyReportPackage;
use App\Models\TaxNonPermanentCalculation;
use App\Models\TaxNonPermanentWorker;
use App\Models\TaxProfile;
use App\Models\TaxRegressionCase;
use App\Models\TaxSimulation;
use App\Models\TaxWithholdingDocument;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaxAdvancedControlController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $openCorrections = TaxCorrection::whereIn('status', ['draft', 'submitted', 'review'])->count();
        $annualDraft = TaxAnnualReconciliation::whereIn('calculation_status', ['draft', 'review'])->count();
        $unpublishedDocuments = TaxWithholdingDocument::whereIn('status', ['draft', 'generated'])->count();
        $failedRegression = TaxRegressionCase::whereIn('run_status', ['failed', 'mismatch'])->count();

        $controls = collect([
            [
                'label' => 'True-up tahunan',
                'value' => TaxAnnualReconciliation::count(),
                'alert' => $annualDraft,
                'status' => $annualDraft > 0 ? 'Perlu review' : 'Terkontrol',
                'note' => 'Rekonsiliasi PPh 21 akhir tahun dan selisih bayar/lebih bayar.',
                'route' => 'payroll.tax-annual',
            ],
            [
                'label' => 'Koreksi pajak',
                'value' => TaxCorrection::count(),
                'alert' => $openCorrections,
                'status' => $openCorrections > 0 ? 'Perlu approval' : 'Terkontrol',
                'note' => 'Koreksi payroll/tax base setelah payroll period berjalan.',
                'route' => 'payroll.tax-corrections',
            ],
            [
                'label' => 'Penghasilan tidak teratur',
                'value' => TaxIrregularIncome::count(),
                'alert' => TaxIrregularIncome::where('calculation_status', 'draft')->count(),
                'status' => TaxIrregularIncome::where('calculation_status', 'draft')->exists() ? 'Perlu final' : 'Terkontrol',
                'note' => 'Bonus, THR, insentif, dan income sekali bayar.',
                'route' => 'payroll.tax-irregular',
            ],
            [
                'label' => 'Pekerja non permanen',
                'value' => TaxNonPermanentWorker::where('is_active', true)->count(),
                'alert' => TaxNonPermanentCalculation::where('calculation_status', 'draft')->count(),
                'status' => TaxNonPermanentCalculation::where('calculation_status', 'draft')->exists() ? 'Perlu final' : 'Terkontrol',
                'note' => 'Harian, borongan, freelancer, dan tenaga tidak tetap.',
                'route' => 'payroll.tax-non-permanent',
            ],
            [
                'label' => 'Sandbox skenario',
                'value' => TaxSimulation::count(),
                'alert' => TaxSimulation::where('scenario_status', 'draft')->count(),
                'status' => TaxSimulation::where('scenario_status', 'draft')->exists() ? 'Draft' : 'Terkontrol',
                'note' => 'Simulasi gross/gross-up/net sebelum angka dipakai payroll.',
                'route' => 'payroll.tax-simulations',
            ],
            [
                'label' => 'Regression pajak',
                'value' => TaxRegressionCase::count(),
                'alert' => $failedRegression,
                'status' => $failedRegression > 0 ? 'Ada mismatch' : 'Terkontrol',
                'note' => 'Test case untuk menjaga formula pajak tetap konsisten.',
                'route' => 'payroll.tax-regression-cases',
            ],
        ]);

        return view('payroll.tax-advanced-control', [
            'active' => 'tax-advanced-control',
            'summary' => [
                'profiles' => TaxProfile::where('is_active', true)->count(),
                'monthly_final' => TaxMonthlyCalculation::where('calculation_status', 'final')->count(),
                'report_packages' => TaxMonthlyReportPackage::count(),
                'withholding_documents' => TaxWithholdingDocument::count(),
                'open_corrections' => $openCorrections,
                'annual_draft' => $annualDraft,
                'unpublished_documents' => $unpublishedDocuments,
                'failed_regression' => $failedRegression,
                'total_pph21' => TaxMonthlyCalculation::sum('pph21_amount'),
                'true_up_amount' => TaxAnnualReconciliation::sum('true_up_amount'),
            ],
            'controls' => $controls,
            'recentCorrections' => TaxCorrection::with('taxProfile')->latest()->limit(8)->get(),
            'recentAnnual' => TaxAnnualReconciliation::with('taxProfile')->latest()->limit(8)->get(),
            'recentSimulations' => TaxSimulation::with('taxProfile')->latest()->limit(8)->get(),
            'recentRegression' => TaxRegressionCase::latest()->limit(8)->get(),
        ]);
    }
}
