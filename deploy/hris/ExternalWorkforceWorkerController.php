<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\ExternalWorkforceContract;
use App\Models\ExternalWorkforceVendor;
use App\Models\ExternalWorkforceWorker;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExternalWorkforceWorkerController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage employees');

        $workersQuery = ExternalWorkforceWorker::with(['vendor', 'contract', 'company', 'branch', 'costCenter'])
            ->latest();

        if ($request->filled('worker_type')) {
            $workersQuery->where('worker_type', $request->worker_type);
        }

        if ($request->filled('status')) {
            $workersQuery->where('status', $request->status);
        }

        $workers = $workersQuery->limit(160)->get();

        return view('external-workforce.workers', [
            'active' => 'external-workforce-workers',
            'vendors' => ExternalWorkforceVendor::orderBy('vendor_code')->get(),
            'contracts' => ExternalWorkforceContract::with('vendor')->orderByDesc('start_date')->get(),
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::with('company')->orderBy('code')->get(),
            'costCenters' => CostCenter::with(['company', 'branch'])->orderBy('code')->get(),
            'workers' => $workers,
            'filters' => $request->only(['worker_type', 'status']),
            'summary' => [
                'total' => $workers->count(),
                'active' => $workers->where('status', 'active')->count(),
                'vendor_invoice' => $workers->where('payment_handling', 'vendor_invoice')->count(),
                'internal_payroll' => $workers->where('payment_handling', 'internal_payroll')->count(),
                'document_incomplete' => $workers->filter(fn (ExternalWorkforceWorker $worker) => collect($worker->document_status ?? [])->contains(false))->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'external_workforce_vendor_id' => ['nullable', 'exists:external_workforce_vendors,id'],
            'external_workforce_contract_id' => ['nullable', 'exists:external_workforce_contracts,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'worker_number' => ['required', 'string', 'max:80', 'unique:external_workforce_workers,worker_number'],
            'full_name' => ['required', 'string', 'max:255'],
            'worker_type' => ['required', Rule::in(['outsource', 'daily_worker', 'piece_rate', 'freelance', 'consultant', 'project_worker'])],
            'identity_number' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:80'],
            'position_title' => ['nullable', 'string', 'max:160'],
            'supervisor_name' => ['nullable', 'string', 'max:160'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'payment_handling' => ['required', Rule::in(['vendor_invoice', 'internal_payroll', 'operational_only'])],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
            'piece_rate' => ['nullable', 'numeric', 'min:0'],
            'has_ktp' => ['nullable', 'boolean'],
            'has_vendor_contract' => ['nullable', 'boolean'],
            'has_hse_induction' => ['nullable', 'boolean'],
            'has_area_access' => ['nullable', 'boolean'],
            'access_area' => ['nullable', 'string', 'max:255'],
            'access_valid_until' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['candidate', 'active', 'inactive', 'blocked', 'ended'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $contract = filled($data['external_workforce_contract_id'] ?? null)
            ? ExternalWorkforceContract::find($data['external_workforce_contract_id'])
            : null;

        $worker = ExternalWorkforceWorker::create([
            ...collect($data)->except(['has_ktp', 'has_vendor_contract', 'has_hse_induction', 'has_area_access', 'access_area', 'access_valid_until'])->all(),
            'external_workforce_vendor_id' => $data['external_workforce_vendor_id'] ?? $contract?->external_workforce_vendor_id,
            'company_id' => $data['company_id'] ?? $contract?->company_id,
            'branch_id' => $data['branch_id'] ?? $contract?->branch_id,
            'cost_center_id' => $data['cost_center_id'] ?? $contract?->cost_center_id,
            'daily_rate' => ((float) ($data['daily_rate'] ?? 0)) > 0 ? $data['daily_rate'] : ($contract?->daily_rate ?? 0),
            'piece_rate' => ((float) ($data['piece_rate'] ?? 0)) > 0 ? $data['piece_rate'] : 0,
            'document_status' => [
                'ktp' => (bool) ($data['has_ktp'] ?? false),
                'vendor_contract' => (bool) ($data['has_vendor_contract'] ?? false),
                'hse_induction' => (bool) ($data['has_hse_induction'] ?? false),
                'area_access' => (bool) ($data['has_area_access'] ?? false),
            ],
            'access_scope' => [
                'area' => $data['access_area'] ?? null,
                'valid_until' => $data['access_valid_until'] ?? null,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'external_workforce_worker.created',
            "Worker external workforce {$worker->worker_number} dibuat.",
            $worker,
            [],
            $worker->toArray(),
            $request,
        );

        return redirect()->route('external-workforce.workers')->with('success', 'Worker external workforce berhasil disimpan.');
    }
}
