<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\ExternalWorkforceContract;
use App\Models\ExternalWorkforceVendor;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExternalWorkforceVendorController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $vendors = ExternalWorkforceVendor::with(['company', 'branch', 'contracts'])
            ->latest()
            ->limit(100)
            ->get();

        $contracts = ExternalWorkforceContract::with(['vendor', 'company', 'branch', 'costCenter'])
            ->latest('start_date')
            ->limit(120)
            ->get();

        return view('external-workforce.vendors', [
            'active' => 'external-workforce-vendors',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::with('company')->orderBy('code')->get(),
            'costCenters' => CostCenter::with(['company', 'branch'])->orderBy('code')->get(),
            'vendors' => $vendors,
            'contracts' => $contracts,
            'summary' => [
                'vendors' => $vendors->count(),
                'active_vendors' => $vendors->where('status', 'active')->count(),
                'active_contracts' => $contracts->where('status', 'active')->count(),
                'expiring_contracts' => $contracts
                    ->filter(fn (ExternalWorkforceContract $contract) => $contract->end_date && $contract->end_date->between(now(), now()->addDays(45)))
                    ->count(),
                'missing_documents' => $contracts->whereNull('document_reference')->count(),
            ],
        ]);
    }

    public function storeVendor(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'vendor_code' => ['required', 'string', 'max:80', 'unique:external_workforce_vendors,vendor_code'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'vendor_type' => ['required', Rule::in(['outsourcing', 'daily_worker_supplier', 'piece_rate_partner', 'consultant_agency', 'security_cleaning', 'other'])],
            'npwp' => ['nullable', 'string', 'max:80'],
            'nib' => ['nullable', 'string', 'max:80'],
            'pic_name' => ['nullable', 'string', 'max:160'],
            'pic_phone' => ['nullable', 'string', 'max:80'],
            'pic_email' => ['nullable', 'email', 'max:160'],
            'address' => ['nullable', 'string', 'max:1000'],
            'has_npwp' => ['nullable', 'boolean'],
            'has_nib' => ['nullable', 'boolean'],
            'has_service_agreement' => ['nullable', 'boolean'],
            'has_hse_commitment' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['prospect', 'active', 'paused', 'blacklisted', 'ended'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $vendor = ExternalWorkforceVendor::create([
            ...collect($data)->except(['has_npwp', 'has_nib', 'has_service_agreement', 'has_hse_commitment'])->all(),
            'compliance_snapshot' => [
                'npwp' => (bool) ($data['has_npwp'] ?? false),
                'nib' => (bool) ($data['has_nib'] ?? false),
                'service_agreement' => (bool) ($data['has_service_agreement'] ?? false),
                'hse_commitment' => (bool) ($data['has_hse_commitment'] ?? false),
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'external_workforce_vendor.created',
            "Vendor external workforce {$vendor->vendor_code} dibuat.",
            $vendor,
            [],
            $vendor->toArray(),
            $request,
        );

        return redirect()->route('external-workforce.vendors')->with('success', 'Vendor outsourcing berhasil disimpan.');
    }

    public function storeContract(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'external_workforce_vendor_id' => ['required', 'exists:external_workforce_vendors,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'contract_number' => ['required', 'string', 'max:100', 'unique:external_workforce_contracts,contract_number'],
            'contract_title' => ['required', 'string', 'max:255'],
            'workforce_scope' => ['required', Rule::in(['outsourced_manpower', 'daily_worker', 'piece_rate', 'freelance_consultant', 'mixed'])],
            'worker_type_scope' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'renewal_reminder_date' => ['nullable', 'date'],
            'headcount_quota' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'monthly_service_fee' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'overtime_rate' => ['nullable', 'numeric', 'min:0'],
            'sla_summary' => ['nullable', 'string', 'max:255'],
            'document_reference' => ['nullable', 'string', 'max:255'],
            'location_scope_note' => ['nullable', 'string', 'max:500'],
            'tax_responsibility' => ['required', Rule::in(['vendor', 'company', 'shared'])],
            'bpjs_responsibility' => ['required', Rule::in(['vendor', 'company', 'shared'])],
            'hse_responsibility' => ['required', Rule::in(['vendor', 'company', 'shared'])],
            'access_responsibility' => ['required', Rule::in(['vendor', 'company', 'shared'])],
            'status' => ['required', Rule::in(['draft', 'active', 'expiring', 'ended', 'terminated'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $vendor = ExternalWorkforceVendor::findOrFail($data['external_workforce_vendor_id']);

        $contract = ExternalWorkforceContract::create([
            ...collect($data)->except([
                'location_scope_note',
                'tax_responsibility',
                'bpjs_responsibility',
                'hse_responsibility',
                'access_responsibility',
            ])->all(),
            'headcount_quota' => $data['headcount_quota'] ?? 0,
            'monthly_service_fee' => $data['monthly_service_fee'] ?? 0,
            'daily_rate' => $data['daily_rate'] ?? 0,
            'overtime_rate' => $data['overtime_rate'] ?? 0,
            'location_scope' => [
                'company_id' => $data['company_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'cost_center_id' => $data['cost_center_id'] ?? null,
                'note' => $data['location_scope_note'] ?? null,
            ],
            'responsibility_matrix' => [
                'tax' => $data['tax_responsibility'],
                'bpjs' => $data['bpjs_responsibility'],
                'hse' => $data['hse_responsibility'],
                'access_area' => $data['access_responsibility'],
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'external_workforce_contract.created',
            "Kontrak vendor {$contract->contract_number} untuk {$vendor->vendor_code} dibuat.",
            $contract,
            [],
            $contract->toArray(),
            $request,
        );

        return redirect()->route('external-workforce.vendors')->with('success', 'Kontrak jasa SDM berhasil disimpan.');
    }
}
