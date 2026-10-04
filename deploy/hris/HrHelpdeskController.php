<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\HrHelpdeskMessage;
use App\Models\HrHelpdeskTicket;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrHelpdeskController extends Controller
{
    private const CATEGORIES = [
        'data_karyawan' => 'Data karyawan',
        'absensi' => 'Absensi',
        'cuti' => 'Cuti & izin',
        'payroll' => 'Payroll & benefit',
        'dokumen' => 'Dokumen HR',
        'akses_ess' => 'Akses ESS',
        'lainnya' => 'Lainnya',
    ];

    private const PRIORITIES = [
        'low' => 'Rendah',
        'normal' => 'Normal',
        'high' => 'Tinggi',
        'urgent' => 'Urgent',
    ];

    private const STATUSES = [
        'submitted' => 'Baru',
        'in_progress' => 'Diproses HR',
        'waiting_employee' => 'Menunggu karyawan',
        'resolved' => 'Selesai',
        'closed' => 'Ditutup',
    ];

    public function ess(): View
    {
        $employee = $this->currentEmployee();
        $tickets = collect();

        if ($employee) {
            $tickets = HrHelpdeskTicket::with(['messages.sender', 'assignee'])
                ->where('employee_profile_id', $employee->id)
                ->latest()
                ->limit(60)
                ->get();
        }

        return view('ess.helpdesk', [
            'active' => 'ess-helpdesk',
            'employee' => $employee,
            'tickets' => $tickets,
            'categories' => self::CATEGORIES,
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
            'summary' => [
                'total' => $tickets->count(),
                'open' => $tickets->whereIn('status', ['submitted', 'in_progress'])->count(),
                'waiting' => $tickets->where('status', 'waiting_employee')->count(),
                'overdue' => $tickets->filter(fn ($ticket) => $ticket->sla_due_at && $ticket->sla_due_at->isPast() && ! in_array($ticket->status, ['resolved', 'closed'], true))->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $this->currentEmployee();
        abort_unless($employee, 404, 'Profil karyawan belum terhubung dengan akun login.');

        $validated = $request->validate([
            'ticket_number' => ['required', 'string', 'max:80', 'unique:hr_helpdesk_tickets,ticket_number'],
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(self::PRIORITIES))],
            'subject' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:1500'],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $sla = $this->slaFor($validated['priority']);
        $ticket = HrHelpdeskTicket::create([
            ...$validated,
            'employee_profile_id' => $employee->id,
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'status' => 'submitted',
            'channel' => 'ess_webview',
            'first_response_due_at' => now()->addHours($sla['first_response_hours']),
            'sla_due_at' => now()->addHours($sla['resolution_hours']),
            'created_by' => auth()->id(),
            'metadata' => [
                'category_label' => self::CATEGORIES[$validated['category']],
                'priority_label' => self::PRIORITIES[$validated['priority']],
                'sla_policy' => $sla,
            ],
        ]);

        $ticket->messages()->create([
            'sender_user_id' => auth()->id(),
            'sender_type' => 'employee',
            'message_body' => $validated['description'],
            'attachment_reference' => $validated['attachment_reference'] ?? null,
        ]);

        AuditLogger::record(
            'hr_helpdesk_ticket.submitted',
            "Tiket helpdesk {$ticket->ticket_number} diajukan oleh {$employee->employee_number}.",
            $ticket,
            [],
            $ticket->load('messages')->toArray(),
            $request,
        );

        return redirect()->route('ess.helpdesk')->with('success', 'Tiket helpdesk berhasil dikirim ke HR.');
    }

    public function replyFromEmployee(Request $request, HrHelpdeskTicket $ticket): RedirectResponse
    {
        $employee = $this->currentEmployee();
        abort_unless($employee && $ticket->employee_profile_id === $employee->id, 403);

        $validated = $request->validate([
            'message_body' => ['required', 'string', 'max:1500'],
            'attachment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $ticket->toArray();
        $ticket->messages()->create([
            'sender_user_id' => auth()->id(),
            'sender_type' => 'employee',
            'message_body' => $validated['message_body'],
            'attachment_reference' => $validated['attachment_reference'] ?? null,
        ]);

        if (in_array($ticket->status, ['waiting_employee', 'resolved'], true)) {
            $ticket->update(['status' => 'in_progress', 'resolved_at' => null]);
        }

        AuditLogger::record(
            'hr_helpdesk_ticket.employee_replied',
            "Karyawan membalas tiket helpdesk {$ticket->ticket_number}.",
            $ticket,
            $before,
            $ticket->fresh()->load('messages')->toArray(),
            $request,
        );

        return redirect()->route('ess.helpdesk')->with('success', 'Balasan tiket berhasil dikirim.');
    }

    public function admin(): View
    {
        $this->authorizeHrEditor();

        $tickets = HrHelpdeskTicket::with(['employeeProfile.company', 'employeeProfile.branch', 'messages.sender', 'assignee'])
            ->latest()
            ->limit(120)
            ->get();

        return view('hr-helpdesk', [
            'active' => 'hr-helpdesk',
            'tickets' => $tickets,
            'categories' => self::CATEGORIES,
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
            'hrUsers' => User::orderBy('name')->get(),
            'summary' => [
                'total' => $tickets->count(),
                'submitted' => $tickets->where('status', 'submitted')->count(),
                'in_progress' => $tickets->where('status', 'in_progress')->count(),
                'overdue' => $tickets->filter(fn ($ticket) => $ticket->sla_due_at && $ticket->sla_due_at->isPast() && ! in_array($ticket->status, ['resolved', 'closed'], true))->count(),
            ],
        ]);
    }

    public function update(Request $request, HrHelpdeskTicket $ticket): RedirectResponse
    {
        $this->authorizeHrEditor();

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'hr_reply' => ['nullable', 'string', 'max:1500'],
            'internal_note' => ['nullable', 'string', 'max:1500'],
        ]);

        $before = $ticket->toArray();
        $payload = [
            'status' => $validated['status'],
            'assigned_to' => $validated['assigned_to'] ?? null,
        ];

        if (! $ticket->responded_at && filled($validated['hr_reply'] ?? null)) {
            $payload['responded_at'] = now();
        }
        if (in_array($validated['status'], ['resolved', 'closed'], true)) {
            $payload['resolved_at'] = now();
            $payload['closed_by'] = auth()->id();
        }

        $ticket->update($payload);

        if (filled($validated['hr_reply'] ?? null)) {
            $ticket->messages()->create([
                'sender_user_id' => auth()->id(),
                'sender_type' => 'hr',
                'message_body' => $validated['hr_reply'],
            ]);
        }

        if (filled($validated['internal_note'] ?? null)) {
            $ticket->messages()->create([
                'sender_user_id' => auth()->id(),
                'sender_type' => 'hr',
                'message_body' => $validated['internal_note'],
                'is_internal' => true,
            ]);
        }

        AuditLogger::record(
            'hr_helpdesk_ticket.updated',
            "Tiket helpdesk {$ticket->ticket_number} diupdate HR.",
            $ticket,
            $before,
            $ticket->fresh()->load('messages')->toArray(),
            $request,
        );

        return redirect()->route('hris.helpdesk')->with('success', 'Tiket helpdesk berhasil diperbarui.');
    }

    private function currentEmployee(): ?EmployeeProfile
    {
        return EmployeeProfile::where('user_id', auth()->id())
            ->orWhere('work_email', auth()->user()?->email)
            ->first();
    }

    private function authorizeHrEditor(): void
    {
        $user = auth()->user();

        abort_unless($user?->hasRole('Super Admin') || $user?->hasPermission('edit employees'), 403);
    }

    private function slaFor(string $priority): array
    {
        return match ($priority) {
            'urgent' => ['first_response_hours' => 2, 'resolution_hours' => 8],
            'high' => ['first_response_hours' => 4, 'resolution_hours' => 24],
            'low' => ['first_response_hours' => 12, 'resolution_hours' => 72],
            default => ['first_response_hours' => 8, 'resolution_hours' => 48],
        };
    }
}
