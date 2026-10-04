<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\TaxExportLog;
use App\Models\TaxPrivacyPolicy;
use App\Models\TaxProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaxPrivacyController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $policy = TaxPrivacyPolicy::with('company')->where('is_active', true)->latest()->first();

        return view('payroll.tax-privacy', [
            'active' => 'tax-privacy',
            'policy' => $policy,
            'companies' => Company::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'exports' => TaxExportLog::with(['company', 'requester', 'approver'])->latest()->limit(50)->get(),
            'sampleProfiles' => TaxProfile::with(['company', 'branch'])->latest()->limit(5)->get(),
            'summary' => [
                'policies' => TaxPrivacyPolicy::count(),
                'pending_exports' => TaxExportLog::where('status', 'pending')->count(),
                'approved_exports' => TaxExportLog::where('status', 'approved')->count(),
                'masked_profiles' => TaxProfile::count(),
            ],
        ]);
    }

    public function storePolicy(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:160'],
            'retention_years' => ['required', 'integer', 'min:1', 'max:30'],
            'legal_basis' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['mask_nik'] = $request->boolean('mask_nik');
        $data['mask_npwp'] = $request->boolean('mask_npwp');
        $data['export_requires_approval'] = $request->boolean('export_requires_approval');
        $data['is_active'] = true;

        $policy = TaxPrivacyPolicy::create($data);

        AuditLogger::record(
            'tax_privacy.policy_created',
            "Kebijakan privasi pajak '{$policy->name}' dibuat.",
            $policy,
            [],
            $policy->toArray(),
            $request
        );

        return redirect()->route('payroll.tax-privacy')->with('success', 'Kebijakan privasi pajak berhasil disimpan.');
    }

    public function requestExport(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'export_type' => ['required', 'in:pph21_profile,calculation_audit,coretax_template,bukti_potong'],
            'scope' => ['required', 'in:company,branch,period,employee'],
            'period' => ['nullable', 'date_format:Y-m'],
            'purpose' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $policy = TaxPrivacyPolicy::where('is_active', true)->latest()->first();
        $maskedFields = [];
        if (!$policy || $policy->mask_nik) {
            $maskedFields[] = 'nik';
        }
        if (!$policy || $policy->mask_npwp) {
            $maskedFields[] = 'npwp';
        }

        $export = TaxExportLog::create([
            ...$data,
            'requested_by' => auth()->id(),
            'status' => ($policy && !$policy->export_requires_approval) ? 'approved' : 'pending',
            'approved_by' => ($policy && !$policy->export_requires_approval) ? auth()->id() : null,
            'approved_at' => ($policy && !$policy->export_requires_approval) ? now() : null,
            'expires_at' => now()->addDays(7),
            'masked_fields' => $maskedFields,
        ]);

        AuditLogger::record(
            'tax_export.requested',
            "Permintaan ekspor pajak {$export->export_type} dibuat dengan status {$export->status}.",
            $export,
            [],
            $export->only(['company_id', 'export_type', 'scope', 'period', 'purpose', 'status', 'masked_fields']),
            $request
        );

        return redirect()->route('payroll.tax-privacy')->with('success', 'Permintaan ekspor pajak berhasil dicatat.');
    }

    public static function mask(?string $value, int $visible = 4): string
    {
        if (!$value) {
            return '-';
        }

        $clean = preg_replace('/\s+/', '', $value);
        $length = strlen($clean);

        if ($length <= $visible) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', max(0, $length - $visible)) . substr($clean, -$visible);
    }
}
