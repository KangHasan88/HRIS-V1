<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\ExternalWorkforceContract;
use App\Models\ExternalWorkforcePieceOutput;
use App\Models\ExternalWorkforcePieceRate;
use App\Models\ExternalWorkforceVendor;
use App\Models\ExternalWorkforceWorker;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExternalWorkforcePieceworkController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage employees');

        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to = $request->input('date_to', now()->toDateString());

        $outputs = ExternalWorkforcePieceOutput::with(['rate', 'worker', 'vendor', 'contract'])
            ->whereBetween('work_date', [$from, $to])
            ->when($request->filled('approval_status'), fn ($query) => $query->where('approval_status', $request->approval_status))
            ->latest('work_date')
            ->latest()
            ->limit(180)
            ->get();

        return view('external-workforce.piecework', [
            'active' => 'external-workforce-piecework',
            'vendors' => ExternalWorkforceVendor::orderBy('vendor_code')->get(),
            'contracts' => ExternalWorkforceContract::with('vendor')->orderByDesc('start_date')->get(),
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'costCenters' => CostCenter::orderBy('code')->get(),
            'workers' => ExternalWorkforceWorker::with(['vendor', 'contract'])
                ->whereIn('worker_type', ['piece_rate', 'outsource', 'daily_worker', 'project_worker'])
                ->whereIn('status', ['active', 'candidate'])
                ->orderBy('full_name')
                ->get(),
            'rates' => ExternalWorkforcePieceRate::with(['vendor', 'contract'])
                ->where('status', 'active')
                ->orderBy('rate_code')
                ->get(),
            'outputs' => $outputs,
            'filters' => [
                'date_from' => $from,
                'date_to' => $to,
                'approval_status' => $request->input('approval_status', ''),
            ],
            'summary' => [
                'rates' => ExternalWorkforcePieceRate::where('status', 'active')->count(),
                'outputs' => $outputs->count(),
                'accepted_quantity' => $outputs->sum(fn (ExternalWorkforcePieceOutput $output) => (float) $output->accepted_quantity),
                'ready_handoff' => $outputs->where('handoff_status', 'ready')->count(),
                'gross_amount' => $outputs->sum(fn (ExternalWorkforcePieceOutput $output) => (float) $output->gross_amount),
            ],
        ]);
    }

    public function storeRate(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'external_workforce_vendor_id' => ['nullable', 'exists:external_workforce_vendors,id'],
            'external_workforce_contract_id' => ['nullable', 'exists:external_workforce_contracts,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'rate_code' => ['required', 'string', 'max:80', 'unique:external_workforce_piece_rates,rate_code'],
            'work_name' => ['required', 'string', 'max:255'],
            'work_category' => ['required', Rule::in(['borongan', 'output_based', 'project_task', 'daily_target'])],
            'unit_name' => ['required', 'string', 'max:40'],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
            'minimum_output' => ['nullable', 'numeric', 'min:0'],
            'quality_rule' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive', 'draft'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $contract = filled($data['external_workforce_contract_id'] ?? null)
            ? ExternalWorkforceContract::find($data['external_workforce_contract_id'])
            : null;

        $rate = ExternalWorkforcePieceRate::create([
            ...$data,
            'external_workforce_vendor_id' => $data['external_workforce_vendor_id'] ?? $contract?->external_workforce_vendor_id,
            'company_id' => $data['company_id'] ?? $contract?->company_id,
            'branch_id' => $data['branch_id'] ?? $contract?->branch_id,
            'cost_center_id' => $data['cost_center_id'] ?? $contract?->cost_center_id,
            'minimum_output' => $data['minimum_output'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('external_workforce_piece_rate.created', "Tarif borongan {$rate->rate_code} dibuat.", $rate, [], $rate->toArray(), $request);

        return redirect()->route('external-workforce.piecework')->with('success', 'Master tarif borongan berhasil disimpan.');
    }

    public function storeOutput(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'external_workforce_piece_rate_id' => ['required', 'exists:external_workforce_piece_rates,id'],
            'external_workforce_worker_id' => ['nullable', 'exists:external_workforce_workers,id'],
            'output_number' => ['required', 'string', 'max:80', 'unique:external_workforce_piece_outputs,output_number'],
            'work_date' => ['required', 'date'],
            'team_name' => ['nullable', 'string', 'max:160'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'accepted_quantity' => ['nullable', 'numeric', 'min:0'],
            'rejected_quantity' => ['nullable', 'numeric', 'min:0'],
            'rate_per_unit' => ['nullable', 'numeric', 'min:0'],
            'foreman_name' => ['nullable', 'string', 'max:160'],
            'approval_status' => ['required', Rule::in(['submitted', 'approved', 'rejected'])],
            'evidence_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $rate = ExternalWorkforcePieceRate::findOrFail($data['external_workforce_piece_rate_id']);
        $worker = filled($data['external_workforce_worker_id'] ?? null)
            ? ExternalWorkforceWorker::find($data['external_workforce_worker_id'])
            : null;
        $acceptedQuantity = (float) ($data['accepted_quantity'] ?? $data['quantity']);
        $ratePerUnit = ((float) ($data['rate_per_unit'] ?? 0)) > 0 ? (float) $data['rate_per_unit'] : (float) $rate->rate_per_unit;
        $handoffTarget = $worker?->payment_handling ?: 'vendor_invoice';

        $output = ExternalWorkforcePieceOutput::create([
            ...$data,
            'external_workforce_vendor_id' => $worker?->external_workforce_vendor_id ?? $rate->external_workforce_vendor_id,
            'external_workforce_contract_id' => $worker?->external_workforce_contract_id ?? $rate->external_workforce_contract_id,
            'company_id' => $worker?->company_id ?? $rate->company_id,
            'branch_id' => $worker?->branch_id ?? $rate->branch_id,
            'cost_center_id' => $worker?->cost_center_id ?? $rate->cost_center_id,
            'accepted_quantity' => $acceptedQuantity,
            'rejected_quantity' => $data['rejected_quantity'] ?? max(0, (float) $data['quantity'] - $acceptedQuantity),
            'rate_per_unit' => $ratePerUnit,
            'gross_amount' => $acceptedQuantity * $ratePerUnit,
            'approved_at' => $data['approval_status'] === 'approved' ? Carbon::now() : null,
            'handoff_target' => $handoffTarget,
            'handoff_status' => $this->handoffStatus($handoffTarget, $data['approval_status']),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('external_workforce_piece_output.created', "Output borongan {$output->output_number} dibuat.", $output, [], $output->toArray(), $request);

        return redirect()->route('external-workforce.piecework')->with('success', 'Output borongan berhasil disimpan.');
    }

    public function review(Request $request, ExternalWorkforcePieceOutput $output): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'foreman_name' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $before = $output->toArray();

        $output->update([
            'approval_status' => $data['decision'],
            'foreman_name' => $data['foreman_name'],
            'approved_at' => Carbon::now(),
            'handoff_status' => $this->handoffStatus($output->handoff_target, $data['decision']),
            'notes' => trim(($output->notes ? $output->notes."\n" : '').($data['notes'] ?? '')),
        ]);

        AuditLogger::record('external_workforce_piece_output.reviewed', "Output borongan {$output->output_number} direview mandor.", $output, $before, $output->fresh()->toArray(), $request);

        return redirect()->route('external-workforce.piecework')->with('success', 'Approval mandor berhasil diperbarui.');
    }

    private function handoffStatus(string $target, string $approvalStatus): string
    {
        if ($target === 'operational_only') {
            return 'not_required';
        }

        return $approvalStatus === 'approved' ? 'ready' : 'pending';
    }
}
