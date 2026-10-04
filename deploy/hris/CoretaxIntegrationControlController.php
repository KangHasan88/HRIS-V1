<?php

namespace App\Http\Controllers;

use App\Models\CoretaxConnectorSetting;
use App\Models\CoretaxRetryLog;
use App\Models\CoretaxSubmissionTracker;
use App\Models\TaxMonthlyExportTemplate;
use App\Models\TaxMonthlyReportPackage;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CoretaxIntegrationControlController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $activeCredentials = CoretaxConnectorSetting::where('is_active', true)->count();
        $readyTemplates = TaxMonthlyExportTemplate::whereIn('status', ['ready', 'generated', 'validated'])->count();
        $queued = CoretaxRetryLog::where('status', 'queued')->count();
        $processing = CoretaxRetryLog::where('status', 'processing')->count();
        $failed = CoretaxRetryLog::where('status', 'failed')->count();
        $accepted = CoretaxSubmissionTracker::where('submission_status', 'accepted')->count();
        $rejected = CoretaxSubmissionTracker::where('submission_status', 'rejected')->count();
        $partial = CoretaxSubmissionTracker::where('submission_status', 'partial')->count();
        $difference = CoretaxSubmissionTracker::sum('difference_amount');

        $readinessChecks = collect([
            [
                'label' => 'Credential aktif',
                'value' => $activeCredentials,
                'target' => 1,
                'status' => $activeCredentials > 0 ? 'Siap' : 'Perlu setup',
                'note' => 'Minimal satu credential sandbox/production aktif untuk queue Coretax.',
                'route' => 'payroll.coretax-connector',
            ],
            [
                'label' => 'Template pajak siap',
                'value' => $readyTemplates,
                'target' => 1,
                'status' => $readyTemplates > 0 ? 'Siap' : 'Perlu generate',
                'note' => 'Template export dari laporan bulanan harus tersedia sebelum submit.',
                'route' => 'payroll.tax-report-packages',
            ],
            [
                'label' => 'Queue terkendali',
                'value' => $queued + $processing,
                'target' => 0,
                'status' => ($queued + $processing) <= 5 ? 'Terkontrol' : 'Menumpuk',
                'note' => 'Queue tinggi perlu dicek agar submission tidak tertahan.',
                'route' => 'payroll.coretax-connector',
            ],
            [
                'label' => 'Failed retry',
                'value' => $failed,
                'target' => 0,
                'status' => $failed === 0 ? 'Terkontrol' : 'Perlu retry',
                'note' => 'Failed retry harus ditindaklanjuti sebelum closing pajak.',
                'route' => 'payroll.coretax-connector',
            ],
            [
                'label' => 'Selisih rekonsiliasi',
                'value' => abs((float) $difference),
                'target' => 0,
                'status' => abs((float) $difference) < 1 ? 'Terkontrol' : 'Perlu cek',
                'note' => 'Selisih template vs acknowledgment Coretax perlu direkonsiliasi.',
                'route' => 'payroll.coretax-connector',
                'money' => true,
            ],
        ]);

        $readyCount = $readinessChecks->whereIn('status', ['Siap', 'Terkontrol'])->count();
        $readiness = $readinessChecks->count() > 0 ? round(($readyCount / $readinessChecks->count()) * 100) : 0;

        return view('payroll.coretax-integration', [
            'active' => 'coretax-integration',
            'summary' => [
                'readiness' => $readiness,
                'credentials' => CoretaxConnectorSetting::count(),
                'active_credentials' => $activeCredentials,
                'packages' => TaxMonthlyReportPackage::count(),
                'templates' => TaxMonthlyExportTemplate::count(),
                'ready_templates' => $readyTemplates,
                'queued' => $queued,
                'processing' => $processing,
                'failed' => $failed,
                'submissions' => CoretaxSubmissionTracker::count(),
                'accepted' => $accepted,
                'rejected' => $rejected,
                'partial' => $partial,
                'difference' => $difference,
            ],
            'readinessChecks' => $readinessChecks,
            'settings' => CoretaxConnectorSetting::with('company')->latest()->limit(8)->get(),
            'retryLogs' => CoretaxRetryLog::with(['setting.company', 'template.package.company'])->latest()->limit(12)->get(),
            'submissions' => CoretaxSubmissionTracker::with(['template.package.company', 'retryLog'])->latest()->limit(12)->get(),
            'templates' => TaxMonthlyExportTemplate::with('package.company')->latest()->limit(8)->get(),
        ]);
    }
}
