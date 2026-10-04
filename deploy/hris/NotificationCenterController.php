<?php

namespace App\Http\Controllers;

use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage modules');

        return view('notification-center', [
            'active' => 'notification-center',
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'templates' => NotificationTemplate::latest()->limit(80)->get(),
            'deliveries' => NotificationDelivery::with(['template', 'user'])->latest()->limit(100)->get(),
            'summary' => [
                'templates' => NotificationTemplate::count(),
                'queued' => NotificationDelivery::where('status', 'queued')->count(),
                'sent' => NotificationDelivery::where('status', 'sent')->count(),
                'failed' => NotificationDelivery::where('status', 'failed')->count(),
            ],
        ]);
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        Gate::authorize('manage modules');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'event_key' => ['required', 'string', 'max:120'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['in:in_app,email,whatsapp,push'],
            'subject' => ['nullable', 'string', 'max:160'],
            'body_template' => ['required', 'string', 'max:2000'],
            'variables' => ['nullable', 'string', 'max:500'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $template = NotificationTemplate::create([
            'code' => $this->templateCode($data['event_key']),
            'name' => $data['name'],
            'event_key' => $data['event_key'],
            'channels' => $data['channels'],
            'subject' => $data['subject'] ?? null,
            'body_template' => $data['body_template'],
            'variables_schema' => collect(explode(',', (string) ($data['variables'] ?? '')))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values()
                ->all(),
            'is_enabled' => (bool) ($data['is_enabled'] ?? false),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'notification_template.created',
            "Template notifikasi {$template->code} dibuat.",
            $template,
            [],
            $template->only(['code', 'event_key', 'channels', 'is_enabled']),
            $request
        );

        return redirect()->route('settings.notification-center')->with('success', 'Template notifikasi berhasil dibuat.');
    }

    public function queueDelivery(Request $request): RedirectResponse
    {
        Gate::authorize('manage modules');

        $data = $request->validate([
            'notification_template_id' => ['required', 'exists:notification_templates,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'recipient_name' => ['nullable', 'string', 'max:120'],
            'recipient_email' => ['nullable', 'email', 'max:160'],
            'recipient_phone' => ['nullable', 'string', 'max:40'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'payload_key' => ['nullable', 'string', 'max:80'],
            'payload_value' => ['nullable', 'string', 'max:200'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $template = NotificationTemplate::findOrFail($data['notification_template_id']);
        $user = isset($data['user_id']) ? User::find($data['user_id']) : null;
        $payload = array_filter([
            'name' => $user?->name ?: ($data['recipient_name'] ?? null),
            $data['payload_key'] ?? 'info' => $data['payload_value'] ?? null,
        ]);
        $message = $this->render($template->body_template, $payload);

        $delivery = NotificationDelivery::create([
            'notification_template_id' => $template->id,
            'user_id' => $user?->id,
            'event_key' => $template->event_key,
            'recipient_name' => $user?->name ?: ($data['recipient_name'] ?? null),
            'recipient_email' => $user?->email ?: ($data['recipient_email'] ?? null),
            'recipient_phone' => $data['recipient_phone'] ?? null,
            'channels' => $template->channels,
            'priority' => $data['priority'],
            'status' => 'queued',
            'subject' => $this->render((string) $template->subject, $payload),
            'rendered_message' => $message,
            'payload_snapshot' => $payload,
            'delivery_log' => [
                'queued_at' => now()->toIso8601String(),
                'channels' => $template->channels,
                'provider_status' => 'waiting_worker',
            ],
            'scheduled_at' => $data['scheduled_at'] ?? now(),
        ]);

        AuditLogger::record(
            'notification_delivery.queued',
            "Notifikasi {$delivery->event_key} masuk antrean.",
            $delivery,
            [],
            $delivery->only(['event_key', 'channels', 'priority', 'status']),
            $request
        );

        return redirect()->route('settings.notification-center')->with('success', 'Notifikasi berhasil masuk antrean.');
    }

    private function render(string $template, array $payload): string
    {
        foreach ($payload as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
        }

        return $template;
    }

    private function templateCode(string $eventKey): string
    {
        return 'NTF-' . Str::upper(Str::slug($eventKey, '')) . '-' . Str::upper(Str::random(5));
    }
}
