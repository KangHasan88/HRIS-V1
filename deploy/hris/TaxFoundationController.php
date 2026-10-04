<?php

namespace App\Http\Controllers;

use App\Models\CoretaxConnectorSetting;
use App\Models\EmployeeProfile;
use App\Models\PayrollTaxComponent;
use App\Models\TaxCalculationAudit;
use App\Models\TaxMonthlyCalculation;
use App\Models\TaxProfile;
use App\Models\TaxTerRate;
use App\Models\TaxWithholdingDocument;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaxFoundationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $activeEmployees = EmployeeProfile::where('is_active', true)->count();
        $linkedProfiles = EmployeeProfile::where('is_active', true)->whereNotNull('tax_profile_id')->count();
        $activeTaxProfiles = TaxProfile::where('is_active', true)->count();
        $verifiedNpwp = TaxProfile::where('is_active', true)->where('is_npwp_verified', true)->count();
        $withoutNpwp = TaxProfile::where('is_active', true)->where(function ($query) {
            $query->whereNull('npwp')->orWhere('npwp', '');
        })->count();

        $checks = collect([
            [
                'label' => 'Profil PPh 21 aktif',
                'value' => $activeTaxProfiles,
                'target' => max(1, $activeEmployees),
                'status' => $activeTaxProfiles >= $activeEmployees && $activeEmployees > 0 ? 'Siap' : 'Perlu cek',
                'note' => 'Setiap karyawan aktif perlu profil pajak untuk payroll dan bukti potong.',
                'route' => 'payroll.tax-profiles',
            ],
            [
                'label' => 'Link karyawan ke profil pajak',
                'value' => $linkedProfiles,
                'target' => max(1, $activeEmployees),
                'status' => $linkedProfiles >= $activeEmployees && $activeEmployees > 0 ? 'Siap' : 'Perlu cek',
                'note' => 'Employee profile harus terhubung ke master PPh 21 agar hitung payroll otomatis.',
                'route' => 'hris.employees',
            ],
            [
                'label' => 'Tarif TER aktif',
                'value' => TaxTerRate::where('is_active', true)->count(),
                'target' => 3,
                'status' => TaxTerRate::where('is_active', true)->count() >= 3 ? 'Siap' : 'Perlu cek',
                'note' => 'Minimal kategori A, B, dan C tersedia untuk dasar hitung bulanan.',
                'route' => 'payroll.tax-monthly',
            ],
            [
                'label' => 'Mapping komponen pajak',
                'value' => PayrollTaxComponent::where('is_active', true)->count(),
                'target' => 1,
                'status' => PayrollTaxComponent::where('is_active', true)->exists() ? 'Siap' : 'Perlu cek',
                'note' => 'Komponen payroll perlu dipetakan sebagai taxable, deduction, non-taxable, atau company cost.',
                'route' => 'payroll.tax-monthly',
            ],
            [
                'label' => 'Coretax connector',
                'value' => CoretaxConnectorSetting::where('is_active', true)->count(),
                'target' => 1,
                'status' => CoretaxConnectorSetting::where('is_active', true)->exists() ? 'Siap' : 'Belum wajib',
                'note' => 'Connector disiapkan untuk export/submission saat integrasi resmi aktif.',
                'route' => 'payroll.coretax-connector',
            ],
        ]);

        $readyCount = $checks->where('status', 'Siap')->count();
        $readiness = $checks->count() > 0 ? round(($readyCount / $checks->count()) * 100) : 0;

        return view('payroll.tax-foundation', [
            'active' => 'tax-foundation',
            'summary' => [
                'active_employees' => $activeEmployees,
                'active_tax_profiles' => $activeTaxProfiles,
                'linked_profiles' => $linkedProfiles,
                'verified_npwp' => $verifiedNpwp,
                'without_npwp' => $withoutNpwp,
                'ter_rates' => TaxTerRate::where('is_active', true)->count(),
                'tax_components' => PayrollTaxComponent::where('is_active', true)->count(),
                'monthly_calculations' => TaxMonthlyCalculation::count(),
                'final_calculations' => TaxMonthlyCalculation::where('calculation_status', 'final')->count(),
                'withholding_documents' => TaxWithholdingDocument::count(),
                'audit_logs' => TaxCalculationAudit::count(),
                'coretax_active' => CoretaxConnectorSetting::where('is_active', true)->count(),
                'readiness' => $readiness,
            ],
            'checks' => $checks,
            'recentCalculations' => TaxMonthlyCalculation::with('taxProfile')->latest()->limit(8)->get(),
            'recentDocuments' => TaxWithholdingDocument::with('taxProfile')->latest()->limit(8)->get(),
            'ptkpDistribution' => TaxProfile::where('is_active', true)
                ->selectRaw('ptkp_status, count(*) as total')
                ->groupBy('ptkp_status')
                ->orderBy('ptkp_status')
                ->get(),
        ]);
    }
}
