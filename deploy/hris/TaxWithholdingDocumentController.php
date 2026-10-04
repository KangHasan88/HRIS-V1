<?php

namespace App\Http\Controllers;

use App\Models\TaxMonthlyReportPackage;
use App\Models\TaxProfile;
use App\Models\TaxWithholdingDocument;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TaxWithholdingDocumentController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-withholding-documents', [
            'active' => 'tax-withholding-documents',
            'packages' => TaxMonthlyReportPackage::with('company')->latest()->limit(50)->get(),
            'documents' => TaxWithholdingDocument::with(['taxProfile', 'package.company'])->latest()->limit(100)->get(),
            'summary' => [
                'documents' => TaxWithholdingDocument::count(),
                'published' => TaxWithholdingDocument::where('status', 'published')->count(),
                'draft' => TaxWithholdingDocument::where('status', 'draft')->count(),
                'total_pph21' => TaxWithholdingDocument::sum('pph21_amount'),
            ],
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'tax_monthly_report_package_id' => ['required', 'exists:tax_monthly_report_packages,id'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $package = TaxMonthlyReportPackage::findOrFail($data['tax_monthly_report_package_id']);
        $created = 0;

        foreach (($package->detail_snapshot ?? []) as $row) {
            $profile = TaxProfile::where('employee_number', $row['employee_number'] ?? null)->first();
            if (! $profile) {
                continue;
            }

            $payload = [
                'period' => $package->period,
                'employee_number' => $row['employee_number'] ?? null,
                'employee_name' => $row['employee_name'] ?? null,
                'company_code' => $row['company_code'] ?? $package->company?->code,
                'ptkp_status' => $row['ptkp_status'] ?? null,
                'gross_income' => $row['gross_income'] ?? 0,
                'tax_base' => $row['tax_base'] ?? 0,
                'pph21_amount' => $row['pph21_amount'] ?? 0,
            ];

            $document = TaxWithholdingDocument::updateOrCreate(
                [
                    'tax_profile_id' => $profile->id,
                    'period' => $package->period,
                    'document_type' => 'monthly_pph21',
                ],
                [
                    'tax_monthly_report_package_id' => $package->id,
                    'company_id' => $package->company_id,
                    'document_number' => 'BUPOT-' . str_replace('-', '', $package->period) . '-' . Str::upper(Str::random(6)),
                    'status' => $data['status'],
                    'gross_income' => $payload['gross_income'],
                    'tax_base' => $payload['tax_base'],
                    'pph21_amount' => $payload['pph21_amount'],
                    'employee_snapshot' => [
                        'employee_number' => $profile->employee_number,
                        'employee_name' => $profile->employee_name,
                        'nik' => $profile->nik,
                        'npwp' => $profile->npwp,
                        'ptkp_status' => $profile->ptkp_status,
                    ],
                    'document_payload' => $payload,
                    'checksum' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE)),
                    'published_at' => $data['status'] === 'published' ? now() : null,
                    'created_by' => auth()->id(),
                ]
            );
            $created++;

            AuditLogger::record(
                'tax_withholding_document.generated',
                "Bukti potong {$document->document_number} dibuat.",
                $document,
                [],
                $document->only(['document_number', 'period', 'status', 'pph21_amount']),
                $request
            );
        }

        return redirect()->route('payroll.tax-withholding-documents')->with('success', "{$created} bukti potong berhasil digenerate.");
    }

    public function ess(): View
    {
        $user = auth()->user();
        $profile = TaxProfile::where('employee_number', $user?->employee_number)
            ->orWhere('employee_name', $user?->name)
            ->first();

        return view('ess.tax-documents', [
            'active' => 'ess-tax-documents',
            'profile' => $profile,
            'documents' => $profile
                ? TaxWithholdingDocument::where('tax_profile_id', $profile->id)->where('status', 'published')->latest()->get()
                : collect(),
        ]);
    }
}
