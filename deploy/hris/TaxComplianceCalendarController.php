<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\TaxComplianceEvent;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TaxComplianceCalendarController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-compliance-calendar', [
            'active' => 'tax-compliance-calendar',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'events' => TaxComplianceEvent::with(['company', 'owner', 'escalationUser'])->orderBy('due_date')->latest('id')->limit(120)->get(),
            'summary' => [
                'total' => TaxComplianceEvent::count(),
                'due_soon' => TaxComplianceEvent::whereDate('due_date', '>=', now()->toDateString())->whereDate('due_date', '<=', now()->addDays(7)->toDateString())->count(),
                'overdue' => TaxComplianceEvent::whereDate('due_date', '<', now()->toDateString())->whereNotIn('status', ['completed', 'cancelled'])->count(),
                'escalated' => TaxComplianceEvent::whereNotNull('escalated_at')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'event_type' => ['required', 'in:monthly_pph21,annual_pph21,coretax_submission,bukti_potong,correction_deadline,internal_review'],
            'title' => ['required', 'string', 'max:160'],
            'period' => ['required', 'date_format:Y-m'],
            'due_date' => ['required', 'date'],
            'reminder_days_before' => ['required', 'integer', 'min:0', 'max:60'],
            'sla_level' => ['required', 'in:normal,high,critical'],
            'status' => ['required', 'in:planned,in_progress,waiting_external,completed,overdue,escalated,cancelled'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'escalation_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $dueDate = Carbon::parse($data['due_date']);
        $reminderDate = $dueDate->copy()->subDays((int) $data['reminder_days_before']);
        $isOverdue = $dueDate->isPast() && !in_array($data['status'], ['completed', 'cancelled'], true);
        $shouldEscalate = $isOverdue || $data['status'] === 'escalated' || $data['sla_level'] === 'critical';

        $event = TaxComplianceEvent::create([
            ...$data,
            'event_number' => $this->eventNumber($data['period']),
            'reminder_date' => $reminderDate->toDateString(),
            'status' => $shouldEscalate && $isOverdue ? 'escalated' : $data['status'],
            'completed_at' => $data['status'] === 'completed' ? now() : null,
            'escalated_at' => $shouldEscalate ? now() : null,
            'checklist_snapshot' => $this->defaultChecklist($data['event_type']),
            'notification_snapshot' => [
                'reminder_date' => $reminderDate->toDateString(),
                'channels' => ['in_app', 'email'],
                'owner_user_id' => $data['owner_user_id'] ?? null,
                'message' => "Reminder {$data['title']} jatuh tempo {$dueDate->format('d M Y')}.",
            ],
            'escalation_snapshot' => [
                'enabled' => (bool) ($data['escalation_user_id'] ?? null),
                'sla_level' => $data['sla_level'],
                'reason' => $shouldEscalate ? ($isOverdue ? 'overdue' : 'critical_sla') : null,
                'escalation_user_id' => $data['escalation_user_id'] ?? null,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'tax_compliance_event.created',
            "Kalender compliance pajak {$event->event_number} dibuat.",
            $event,
            [],
            $event->only(['event_number', 'event_type', 'period', 'due_date', 'status', 'sla_level']),
            $request
        );

        return redirect()->route('payroll.tax-compliance-calendar')->with('success', 'Kalender compliance pajak berhasil dibuat.');
    }

    private function defaultChecklist(string $eventType): array
    {
        return match ($eventType) {
            'monthly_pph21' => ['Final payroll', 'Review PPh 21', 'Generate laporan bulanan', 'Submit Coretax'],
            'annual_pph21' => ['Rekap 12 bulan', 'True-up tahunan', 'Review kurang/lebih bayar', 'Arsip bukti potong'],
            'coretax_submission' => ['Validasi template', 'Submit Coretax', 'Reconcile response', 'Arsip acknowledgement'],
            'bukti_potong' => ['Generate bukti potong', 'Publish ESS', 'Sampling dokumen', 'Lock archive'],
            'correction_deadline' => ['Review koreksi', 'Approval payroll', 'Adjust payroll', 'Update pelaporan'],
            default => ['Review data', 'Approval PIC', 'Selesaikan task', 'Arsip evidence'],
        };
    }

    private function eventNumber(string $period): string
    {
        return 'TAXCAL-' . str_replace('-', '', $period) . '-' . Str::upper(Str::random(6));
    }
}
