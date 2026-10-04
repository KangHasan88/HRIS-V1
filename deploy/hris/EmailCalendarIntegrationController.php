<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\EmailCalendarConnector;
use App\Models\EmailCalendarEvent;
use App\Models\LeaveRequest;
use App\Models\RecruitmentInterview;
use App\Models\TrainingCourse;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmailCalendarIntegrationController extends Controller
{
    public function index(): View
    {
        $this->authorizeIntegration();

        $connectors = EmailCalendarConnector::with(['company', 'branch'])
            ->withCount('events')
            ->latest()
            ->limit(100)
            ->get();
        $events = EmailCalendarEvent::with('connector')->latest('scheduled_start')->limit(120)->get();

        return view('integrations.email-calendar', [
            'active' => 'email-calendar',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'connectors' => $connectors,
            'events' => $events,
            'interviews' => RecruitmentInterview::with('candidate')->latest('scheduled_at')->limit(40)->get(),
            'courses' => TrainingCourse::latest('start_date')->limit(40)->get(),
            'leaveRequests' => LeaveRequest::with('employeeProfile')->latest('start_date')->limit(40)->get(),
            'summary' => [
                'connectors' => $connectors->count(),
                'active' => $connectors->where('status', 'active')->count(),
                'queued_email' => $events->where('email_status', 'queued')->count(),
                'queued_calendar' => $events->where('calendar_status', 'queued')->count(),
            ],
        ]);
    }

    public function storeConnector(Request $request): RedirectResponse
    {
        $this->authorizeIntegration();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'connector_code' => ['required', 'string', 'max:80', 'unique:email_calendar_connectors,connector_code'],
            'connector_name' => ['required', 'string', 'max:255'],
            'provider' => ['required', Rule::in(['google_workspace', 'microsoft_365', 'smtp_ics', 'generic_webhook'])],
            'connection_type' => ['required', Rule::in(['oauth_api', 'smtp_ics', 'webhook'])],
            'sender_email' => ['nullable', 'email', 'max:255'],
            'calendar_owner' => ['nullable', 'string', 'max:255'],
            'endpoint_url' => ['nullable', 'string', 'max:255'],
            'auth_mode' => ['required', Rule::in(['oauth', 'api_key', 'basic_auth', 'smtp_auth'])],
            'default_timezone' => ['required', 'string', 'max:80'],
            'enabled_modules' => ['nullable', 'array'],
            'enabled_modules.*' => [Rule::in(['interview', 'training', 'leave'])],
            'status' => ['required', Rule::in(['draft', 'active', 'paused', 'error'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $connector = EmailCalendarConnector::create([
            ...$validated,
            'enabled_modules' => $validated['enabled_modules'] ?? [],
            'mapping_config' => [
                'email_subject_template' => '{{event_title}}',
                'calendar_uid_strategy' => 'source_type-source_id-event_number',
                'default_reminder_minutes' => 30,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('email_calendar_connector.created', "Connector email/kalender {$connector->connector_code} dibuat.", $connector, [], $connector->toArray(), $request);

        return redirect()->route('integrations.email-calendar')->with('success', 'Connector email dan kalender berhasil dibuat.');
    }

    public function queueEvent(Request $request): RedirectResponse
    {
        $this->authorizeIntegration();

        $validated = $request->validate([
            'email_calendar_connector_id' => ['required', 'exists:email_calendar_connectors,id'],
            'source_type' => ['required', Rule::in(['interview', 'training', 'leave', 'manual'])],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'event_number' => ['required', 'string', 'max:80', 'unique:email_calendar_events,event_number'],
            'event_title' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'scheduled_start' => ['nullable', 'date'],
            'scheduled_end' => ['nullable', 'date', 'after_or_equal:scheduled_start'],
            'location_or_link' => ['nullable', 'string', 'max:255'],
            'send_email' => ['nullable', 'boolean'],
            'create_calendar_event' => ['nullable', 'boolean'],
        ]);

        $source = $this->resolveSource($validated['source_type'], $validated['source_id'] ?? null);

        $event = EmailCalendarEvent::create([
            'email_calendar_connector_id' => $validated['email_calendar_connector_id'],
            'event_number' => $validated['event_number'],
            'source_type' => $validated['source_type'],
            'source_id' => $validated['source_id'] ?? null,
            'event_title' => $validated['event_title'] ?: $source['title'],
            'recipient_name' => $validated['recipient_name'] ?: $source['recipient_name'],
            'recipient_email' => $validated['recipient_email'] ?: $source['recipient_email'],
            'scheduled_start' => $validated['scheduled_start'] ?: $source['scheduled_start'],
            'scheduled_end' => $validated['scheduled_end'] ?: $source['scheduled_end'],
            'location_or_link' => $validated['location_or_link'] ?: $source['location_or_link'],
            'send_email' => $request->boolean('send_email', true),
            'create_calendar_event' => $request->boolean('create_calendar_event', true),
            'email_status' => $request->boolean('send_email', true) ? 'queued' : 'skipped',
            'calendar_status' => $request->boolean('create_calendar_event', true) ? 'queued' : 'skipped',
            'payload_snapshot' => [
                'source_summary' => $source,
                'queued_at' => now()->toIso8601String(),
                'delivery_mode' => 'email_calendar_queue',
            ],
            'created_by' => auth()->id(),
        ]);

        $event->connector()->update(['last_sync_at' => now()]);

        AuditLogger::record('email_calendar_event.queued', "Event email/kalender {$event->event_number} masuk antrean.", $event, [], $event->toArray(), $request);

        return redirect()->route('integrations.email-calendar')->with('success', 'Event email dan kalender berhasil masuk antrean.');
    }

    private function resolveSource(string $sourceType, ?int $sourceId): array
    {
        if ($sourceType === 'interview' && $sourceId) {
            $interview = RecruitmentInterview::with('candidate')->findOrFail($sourceId);

            return [
                'title' => 'Interview ' . $interview->candidate?->full_name . ' - ' . ucfirst($interview->interview_stage),
                'recipient_name' => $interview->candidate?->full_name,
                'recipient_email' => $interview->candidate?->email,
                'scheduled_start' => $interview->scheduled_at,
                'scheduled_end' => $interview->scheduled_at?->copy()->addMinutes((int) $interview->duration_minutes),
                'location_or_link' => $interview->location_or_link,
            ];
        }

        if ($sourceType === 'training' && $sourceId) {
            $course = TrainingCourse::findOrFail($sourceId);

            return [
                'title' => 'Training ' . $course->title,
                'recipient_name' => $course->provider,
                'recipient_email' => null,
                'scheduled_start' => $course->start_date?->startOfDay(),
                'scheduled_end' => $course->end_date?->endOfDay(),
                'location_or_link' => $course->delivery_mode,
            ];
        }

        if ($sourceType === 'leave' && $sourceId) {
            $leave = LeaveRequest::with('employeeProfile')->findOrFail($sourceId);

            return [
                'title' => 'Cuti ' . $leave->employeeProfile?->full_name,
                'recipient_name' => $leave->employeeProfile?->full_name,
                'recipient_email' => $leave->employeeProfile?->work_email ?: $leave->employeeProfile?->personal_email,
                'scheduled_start' => $leave->start_date?->startOfDay(),
                'scheduled_end' => $leave->end_date?->endOfDay(),
                'location_or_link' => 'Leave calendar',
            ];
        }

        return [
            'title' => 'Event manual HRIS',
            'recipient_name' => null,
            'recipient_email' => null,
            'scheduled_start' => now()->addDay(),
            'scheduled_end' => now()->addDay()->addHour(),
            'location_or_link' => null,
        ];
    }

    private function authorizeIntegration(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage modules') || $user?->hasPermission('view attendance'), 403);
    }
}
