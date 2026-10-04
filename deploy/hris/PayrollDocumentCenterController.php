<?php

namespace App\Http\Controllers;

use App\Models\PayrollPayslip;
use App\Models\TaxWithholdingDocument;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PayrollDocumentCenterController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $totalPayslips = PayrollPayslip::count();
        $publishedPayslips = PayrollPayslip::where('status', 'published')->count();
        $acknowledgedPayslips = PayrollPayslip::whereNotNull('acknowledged_at')->count();
        $totalTaxDocuments = TaxWithholdingDocument::count();
        $publishedTaxDocuments = TaxWithholdingDocument::where('status', 'published')->count();
        $draftTaxDocuments = TaxWithholdingDocument::where('status', 'draft')->count();

        $checks = collect([
            [
                'label' => 'Slip gaji published',
                'value' => $publishedPayslips,
                'target' => max(1, $totalPayslips),
                'status' => $totalPayslips > 0 && $publishedPayslips >= $totalPayslips ? 'Siap ESS' : 'Perlu publish',
                'note' => 'Slip published akan muncul di ESS dan bisa di-acknowledge karyawan.',
                'route' => 'payroll.payslips',
            ],
            [
                'label' => 'Acknowledgement karyawan',
                'value' => $acknowledgedPayslips,
                'target' => max(1, $publishedPayslips),
                'status' => $publishedPayslips > 0 && $acknowledgedPayslips >= $publishedPayslips ? 'Lengkap' : 'Perlu follow-up',
                'note' => 'Pantau slip yang belum dikonfirmasi oleh karyawan.',
                'route' => 'payroll.payslips',
            ],
            [
                'label' => 'Bukti potong published',
                'value' => $publishedTaxDocuments,
                'target' => max(1, $totalTaxDocuments),
                'status' => $totalTaxDocuments > 0 && $publishedTaxDocuments >= $totalTaxDocuments ? 'Siap ESS' : 'Perlu publish',
                'note' => 'Dokumen pajak published bisa diakses lewat ESS.',
                'route' => 'payroll.tax-withholding-documents',
            ],
            [
                'label' => 'Draft dokumen pajak',
                'value' => $draftTaxDocuments,
                'target' => 0,
                'status' => $draftTaxDocuments === 0 ? 'Terkontrol' : 'Perlu review',
                'note' => 'Draft perlu direview sebelum dipublish atau disinkronkan ke proses pajak.',
                'route' => 'payroll.tax-withholding-documents',
            ],
        ]);

        $readyCount = $checks->whereIn('status', ['Siap ESS', 'Lengkap', 'Terkontrol'])->count();

        return view('payroll.document-center', [
            'active' => 'payroll-document-center',
            'summary' => [
                'readiness' => $checks->count() > 0 ? round(($readyCount / $checks->count()) * 100) : 0,
                'payslips' => $totalPayslips,
                'published_payslips' => $publishedPayslips,
                'acknowledged_payslips' => $acknowledgedPayslips,
                'resend_count' => PayrollPayslip::sum('resend_count'),
                'tax_documents' => $totalTaxDocuments,
                'published_tax_documents' => $publishedTaxDocuments,
                'draft_tax_documents' => $draftTaxDocuments,
                'net_pay' => PayrollPayslip::sum('net_pay'),
                'pph21_documents' => TaxWithholdingDocument::sum('pph21_amount'),
            ],
            'checks' => $checks,
            'payslips' => PayrollPayslip::with(['employee.company', 'company'])->latest()->limit(12)->get(),
            'taxDocuments' => TaxWithholdingDocument::with(['taxProfile', 'package.company'])->latest()->limit(12)->get(),
        ]);
    }
}
