<?php

namespace App\Http\Controllers;

use App\Models\ExternalWorkforceComplianceItem;
use App\Models\ExternalWorkforceContract;
use App\Models\ExternalWorkforceVendor;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExternalWorkforceComplianceController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $items = ExternalWorkforceComplianceItem::with(['vendor', 'contract'])
            ->latest('expiry_date')
            ->latest()
            ->limit(150)
            ->get();

        return view('external-workforce.compliance', [
            'active' => 'external-workforce-compliance',
            'vendors' => ExternalWorkforceVendor::orderBy('vendor_code')->get(),
            'contracts' => ExternalWorkforceContract::with('vendor')->orderByDesc('start_date')->get(),
            'items' => $items,
            'summary' => [
                'total' => $items->count(),
                'verified' => $items->where('verification_status', 'verified')->count(),
                'need_revision' => $items->whereIn('verification_status', ['need_revision', 'expired'])->count(),
                'high_risk' => $items->where('risk_level', 'high')->count(),
                'expiring' => $items
                    ->filter(fn (ExternalWorkforceComplianceItem $item) => $item->expiry_date && $item->expiry_date->between(now(), now()->addDays(45)))
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'external_workforce_vendor_id' => ['required', 'exists:external_workforce_vendors,id'],
            'external_workforce_contract_id' => ['nullable', 'exists:external_workforce_contracts,id'],
            'item_number' => ['required', 'string', 'max:100', 'unique:external_workforce_compliance_items,item_number'],
            'compliance_area' => ['required', Rule::in(['tax', 'bpjs', 'hse', 'access_area', 'legal_contract', 'data_privacy', 'other'])],
            'requirement_name' => ['required', 'string', 'max:255'],
            'responsible_party' => ['required', Rule::in(['vendor', 'company', 'shared'])],
            'document_reference' => ['nullable', 'string', 'max:255'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'verification_status' => ['required', Rule::in(['draft', 'submitted', 'verified', 'need_revision', 'expired', 'waived'])],
            'risk_level' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'owner_name' => ['nullable', 'string', 'max:160'],
            'next_follow_up_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = ExternalWorkforceComplianceItem::create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'external_workforce_compliance.created',
            "Compliance outsourcing {$item->item_number} dibuat.",
            $item,
            [],
            $item->toArray(),
            $request,
        );

        return redirect()->route('external-workforce.compliance')->with('success', 'Compliance outsourcing berhasil disimpan.');
    }
}
