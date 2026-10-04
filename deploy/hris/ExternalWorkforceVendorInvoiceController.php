<?php

namespace App\Http\Controllers;

use App\Models\ExternalWorkforceAttendance;
use App\Models\ExternalWorkforcePieceOutput;
use App\Models\ExternalWorkforceVendor;
use App\Models\ExternalWorkforceVendorInvoice;
use App\Models\ExternalWorkforceVendorInvoiceLine;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExternalWorkforceVendorInvoiceController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage employees');

        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to = $request->input('date_to', now()->toDateString());

        $invoices = ExternalWorkforceVendorInvoice::with(['vendor', 'contract', 'lines.worker'])
            ->whereBetween('period_start', [$from, $to])
            ->latest()
            ->limit(100)
            ->get();

        return view('external-workforce.vendor-invoices', [
            'active' => 'external-workforce-vendor-invoices',
            'vendors' => ExternalWorkforceVendor::with('contracts')->orderBy('vendor_code')->get(),
            'invoices' => $invoices,
            'attendanceSources' => ExternalWorkforceAttendance::with(['worker', 'vendor'])
                ->where('approval_status', 'approved')
                ->where('handoff_target', 'vendor_invoice')
                ->whereIn('handoff_status', ['ready', 'sent'])
                ->whereBetween('attendance_date', [$from, $to])
                ->latest('attendance_date')
                ->limit(160)
                ->get(),
            'pieceworkSources' => ExternalWorkforcePieceOutput::with(['worker', 'rate', 'vendor'])
                ->where('approval_status', 'approved')
                ->where('handoff_target', 'vendor_invoice')
                ->whereIn('handoff_status', ['ready', 'sent'])
                ->whereBetween('work_date', [$from, $to])
                ->latest('work_date')
                ->limit(160)
                ->get(),
            'filters' => ['date_from' => $from, 'date_to' => $to],
            'summary' => [
                'invoices' => $invoices->count(),
                'system_amount' => $invoices->sum(fn (ExternalWorkforceVendorInvoice $invoice) => (float) $invoice->system_amount),
                'claimed_amount' => $invoices->sum(fn (ExternalWorkforceVendorInvoice $invoice) => (float) $invoice->claimed_amount),
                'variance_amount' => $invoices->sum(fn (ExternalWorkforceVendorInvoice $invoice) => (float) abs((float) $invoice->variance_amount)),
                'ready_payment' => $invoices->where('payment_status', 'ready')->count(),
            ],
        ]);
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'external_workforce_vendor_id' => ['required', 'exists:external_workforce_vendors,id'],
            'external_workforce_contract_id' => ['nullable', 'exists:external_workforce_contracts,id'],
            'invoice_number' => ['required', 'string', 'max:80', 'unique:external_workforce_vendor_invoices,invoice_number'],
            'vendor_invoice_number' => ['nullable', 'string', 'max:120'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'source_scope' => ['required', Rule::in(['mandays', 'piecework', 'mixed', 'manual_adjustment'])],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $vendor = ExternalWorkforceVendor::findOrFail($data['external_workforce_vendor_id']);
        $contract = filled($data['external_workforce_contract_id'] ?? null) ? $vendor->contracts()->find($data['external_workforce_contract_id']) : null;

        $invoice = ExternalWorkforceVendorInvoice::create([
            ...$data,
            'company_id' => $contract?->company_id ?? $vendor->company_id,
            'branch_id' => $contract?->branch_id ?? $vendor->branch_id,
            'cost_center_id' => $contract?->cost_center_id,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('external_workforce_vendor_invoice.created', "Invoice vendor {$invoice->invoice_number} dibuat.", $invoice, [], $invoice->toArray(), $request);

        return redirect()->route('external-workforce.vendor-invoices')->with('success', 'Invoice vendor berhasil dibuat.');
    }

    public function storeLine(Request $request, ExternalWorkforceVendorInvoice $invoice): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'source_type' => ['required', Rule::in(['attendance', 'piece_output', 'manual_adjustment'])],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_name' => ['nullable', 'string', 'max:40'],
            'system_rate' => ['nullable', 'numeric', 'min:0'],
            'claimed_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $source = $this->resolveSource($data['source_type'], $data['source_id'] ?? null);
        $linePayload = $this->linePayloadFromSource($data, $source);

        $line = $invoice->lines()->create($linePayload);

        $this->refreshInvoiceTotals($invoice);

        AuditLogger::record('external_workforce_vendor_invoice_line.created', "Line invoice vendor {$invoice->invoice_number} dibuat.", $line, [], $line->toArray(), $request);

        return redirect()->route('external-workforce.vendor-invoices')->with('success', 'Line rekonsiliasi invoice berhasil ditambahkan.');
    }

    public function review(Request $request, ExternalWorkforceVendorInvoice $invoice): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'needs_revision', 'rejected'])],
            'reviewed_by' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $before = $invoice->toArray();
        $paymentStatus = $data['decision'] === 'approved' ? 'ready' : 'not_ready';
        $reconciliationStatus = $data['decision'] === 'needs_revision' ? 'needs_revision' : ($invoice->variance_amount == 0 ? 'matched' : 'variance');

        $invoice->update([
            'approval_status' => $data['decision'],
            'reconciliation_status' => $reconciliationStatus,
            'payment_status' => $paymentStatus,
            'reviewed_by' => $data['reviewed_by'],
            'reviewed_at' => Carbon::now(),
            'notes' => trim(($invoice->notes ? $invoice->notes."\n" : '').($data['notes'] ?? '')),
        ]);

        AuditLogger::record('external_workforce_vendor_invoice.reviewed', "Invoice vendor {$invoice->invoice_number} direview.", $invoice, $before, $invoice->fresh()->toArray(), $request);

        return redirect()->route('external-workforce.vendor-invoices')->with('success', 'Review invoice vendor berhasil disimpan.');
    }

    private function resolveSource(string $sourceType, ?int $sourceId): ExternalWorkforceAttendance|ExternalWorkforcePieceOutput|null
    {
        if (! $sourceId || $sourceType === 'manual_adjustment') {
            return null;
        }

        return match ($sourceType) {
            'attendance' => ExternalWorkforceAttendance::with('worker')->findOrFail($sourceId),
            'piece_output' => ExternalWorkforcePieceOutput::with(['worker', 'rate'])->findOrFail($sourceId),
            default => null,
        };
    }

    private function linePayloadFromSource(array $data, ExternalWorkforceAttendance|ExternalWorkforcePieceOutput|null $source): array
    {
        if ($source instanceof ExternalWorkforceAttendance) {
            $quantity = (float) $source->mandays;
            $systemAmount = (float) $source->gross_amount;

            return [
                'external_workforce_worker_id' => $source->external_workforce_worker_id,
                'source_type' => 'attendance',
                'source_id' => $source->id,
                'work_date' => $source->attendance_date,
                'description' => ($data['description'] ?? null) ?: "Mandays {$source->worker?->full_name}",
                'quantity' => $quantity,
                'unit_name' => 'mandays',
                'system_rate' => $quantity > 0 ? $systemAmount / $quantity : (float) $source->daily_rate,
                'system_amount' => $systemAmount,
                'claimed_amount' => $data['claimed_amount'],
                'variance_amount' => (float) $data['claimed_amount'] - $systemAmount,
                'reconciliation_status' => abs((float) $data['claimed_amount'] - $systemAmount) <= 1 ? 'matched' : 'variance',
                'notes' => $data['notes'] ?? null,
            ];
        }

        if ($source instanceof ExternalWorkforcePieceOutput) {
            $systemAmount = (float) $source->gross_amount;

            return [
                'external_workforce_worker_id' => $source->external_workforce_worker_id,
                'source_type' => 'piece_output',
                'source_id' => $source->id,
                'work_date' => $source->work_date,
                'description' => ($data['description'] ?? null) ?: "Output {$source->rate?->work_name}",
                'quantity' => $source->accepted_quantity,
                'unit_name' => $source->rate?->unit_name ?: 'unit',
                'system_rate' => $source->rate_per_unit,
                'system_amount' => $systemAmount,
                'claimed_amount' => $data['claimed_amount'],
                'variance_amount' => (float) $data['claimed_amount'] - $systemAmount,
                'reconciliation_status' => abs((float) $data['claimed_amount'] - $systemAmount) <= 1 ? 'matched' : 'variance',
                'notes' => $data['notes'] ?? null,
            ];
        }

        $quantity = (float) ($data['quantity'] ?? 1);
        $systemRate = (float) ($data['system_rate'] ?? 0);
        $systemAmount = $quantity * $systemRate;

        return [
            'source_type' => 'manual_adjustment',
            'source_id' => null,
            'work_date' => null,
            'description' => ($data['description'] ?? null) ?: 'Manual adjustment invoice vendor',
            'quantity' => $quantity,
            'unit_name' => $data['unit_name'] ?? 'unit',
            'system_rate' => $systemRate,
            'system_amount' => $systemAmount,
            'claimed_amount' => $data['claimed_amount'],
            'variance_amount' => (float) $data['claimed_amount'] - $systemAmount,
            'reconciliation_status' => abs((float) $data['claimed_amount'] - $systemAmount) <= 1 ? 'matched' : 'variance',
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function refreshInvoiceTotals(ExternalWorkforceVendorInvoice $invoice): void
    {
        $lines = $invoice->lines()->get();
        $systemAmount = $lines->sum(fn (ExternalWorkforceVendorInvoiceLine $line) => (float) $line->system_amount);
        $claimedAmount = $lines->sum(fn (ExternalWorkforceVendorInvoiceLine $line) => (float) $line->claimed_amount);
        $taxAmount = (float) $invoice->tax_amount;

        $invoice->forceFill([
            'system_amount' => $systemAmount,
            'claimed_amount' => $claimedAmount,
            'variance_amount' => $claimedAmount - $systemAmount,
            'payable_amount' => $claimedAmount + $taxAmount,
            'reconciliation_status' => $lines->contains(fn (ExternalWorkforceVendorInvoiceLine $line) => $line->reconciliation_status === 'variance') ? 'variance' : 'matched',
            'approval_status' => 'submitted',
            'payment_status' => 'not_ready',
        ])->save();
    }
}
