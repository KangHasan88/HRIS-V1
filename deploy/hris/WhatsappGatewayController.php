<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\WhatsappGatewaySetting;
use App\Models\WhatsappMessageQueue;
use App\Models\WhatsappWebhookLog;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WhatsappGatewayController extends Controller
{
    private const EVENTS = [
        'approval_request' => 'Approval request',
        'approval_reminder' => 'Reminder approval',
        'payslip_published' => 'Slip gaji publish',
        'tax_document_published' => 'Bukti potong publish',
        'attendance_alert' => 'Alert absensi',
        'manual' => 'Manual',
    ];

    public function index(): View
    {
        $this->authorizeIntegration();

        $gateways = WhatsappGatewaySetting::with(['company', 'branch'])->withCount('messages')->latest()->limit(80)->get();
        $messages = WhatsappMessageQueue::with('gateway')->latest()->limit(120)->get();
        $webhooks = WhatsappWebhookLog::with(['gateway', 'message'])->latest('received_at')->limit(80)->get();

        return view('integrations.whatsapp-gateway', [
            'active' => 'whatsapp-gateway',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::orderBy('code')->get(),
            'gateways' => $gateways,
            'messages' => $messages,
            'webhooks' => $webhooks,
            'events' => self::EVENTS,
            'summary' => [
                'gateways' => $gateways->count(),
                'active' => $gateways->where('status', 'active')->count(),
                'queued' => $messages->where('status', 'queued')->count(),
                'failed' => $messages->where('status', 'failed')->count(),
            ],
        ]);
    }

    public function storeGateway(Request $request): RedirectResponse
    {
        $this->authorizeIntegration();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'gateway_code' => ['required', 'string', 'max:80', 'unique:whatsapp_gateway_settings,gateway_code'],
            'gateway_name' => ['required', 'string', 'max:255'],
            'provider' => ['required', Rule::in(['meta_cloud_api', 'wablas', 'qontak', 'fonnte', 'generic_webhook'])],
            'connection_type' => ['required', Rule::in(['cloud_api', 'webhook_queue', 'local_bridge'])],
            'endpoint_url' => ['nullable', 'string', 'max:255'],
            'sender_id' => ['nullable', 'string', 'max:80'],
            'auth_mode' => ['required', Rule::in(['bearer_token', 'api_key', 'basic_auth', 'none'])],
            'status' => ['required', Rule::in(['draft', 'active', 'paused', 'error'])],
            'enabled_events' => ['nullable', 'array'],
            'enabled_events.*' => [Rule::in(array_keys(self::EVENTS))],
            'masked_token' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $gateway = WhatsappGatewaySetting::create([
            ...collect($validated)->except(['masked_token'])->all(),
            'enabled_events' => $validated['enabled_events'] ?? [],
            'credential_snapshot' => [
                'masked_token' => $validated['masked_token'] ?? null,
                'stored_mode' => 'masked_only',
            ],
            'last_ping_at' => $validated['status'] === 'active' ? now() : null,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('whatsapp_gateway.created', "Gateway WhatsApp {$gateway->gateway_code} dibuat.", $gateway, [], $gateway->toArray(), $request);

        return redirect()->route('integrations.whatsapp-gateway')->with('success', 'Gateway WhatsApp berhasil dibuat.');
    }

    public function queueMessage(Request $request): RedirectResponse
    {
        $this->authorizeIntegration();

        $validated = $request->validate([
            'whatsapp_gateway_setting_id' => ['required', 'exists:whatsapp_gateway_settings,id'],
            'message_number' => ['required', 'string', 'max:80', 'unique:whatsapp_message_queues,message_number'],
            'event_key' => ['required', Rule::in(array_keys(self::EVENTS))],
            'source_type' => ['nullable', 'string', 'max:80'],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'recipient_name' => ['nullable', 'string', 'max:120'],
            'recipient_phone' => ['required', 'string', 'max:40'],
            'template_name' => ['nullable', 'string', 'max:120'],
            'message_body' => ['required', 'string', 'max:2000'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $message = WhatsappMessageQueue::create([
            ...$validated,
            'status' => 'queued',
            'scheduled_at' => $validated['scheduled_at'] ?? now(),
            'payload_snapshot' => [
                'event_label' => self::EVENTS[$validated['event_key']],
                'queued_from' => 'web_admin',
                'queued_at' => now()->toIso8601String(),
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record('whatsapp_message.queued', "Pesan WhatsApp {$message->message_number} masuk antrean.", $message, [], $message->toArray(), $request);

        return redirect()->route('integrations.whatsapp-gateway')->with('success', 'Pesan WhatsApp berhasil masuk antrean.');
    }

    public function simulateWebhook(Request $request, WhatsappMessageQueue $message): RedirectResponse
    {
        $this->authorizeIntegration();

        $validated = $request->validate([
            'delivery_status' => ['required', Rule::in(['sent', 'delivered', 'read', 'failed'])],
            'provider_message_id' => ['nullable', 'string', 'max:120'],
        ]);

        $before = $message->toArray();
        $message->update([
            'status' => $validated['delivery_status'] === 'failed' ? 'failed' : $validated['delivery_status'],
            'sent_at' => in_array($validated['delivery_status'], ['sent', 'delivered', 'read'], true) ? now() : $message->sent_at,
            'attempt_count' => $message->attempt_count + 1,
            'provider_response' => [
                'delivery_status' => $validated['delivery_status'],
                'provider_message_id' => $validated['provider_message_id'] ?? ('SIM-' . $message->id),
                'received_at' => now()->toIso8601String(),
            ],
        ]);

        $log = WhatsappWebhookLog::create([
            'whatsapp_gateway_setting_id' => $message->whatsapp_gateway_setting_id,
            'whatsapp_message_queue_id' => $message->id,
            'webhook_number' => 'WA-WH-' . now()->format('YmdHis') . '-' . $message->id,
            'event_type' => 'delivery_status',
            'provider_message_id' => $validated['provider_message_id'] ?? ('SIM-' . $message->id),
            'delivery_status' => $validated['delivery_status'],
            'payload' => [
                'simulation' => true,
                'message_number' => $message->message_number,
            ],
            'received_at' => now(),
        ]);

        AuditLogger::record('whatsapp_webhook.received', "Webhook WhatsApp {$log->webhook_number} diproses.", $message, $before, $message->fresh()->toArray(), $request);

        return redirect()->route('integrations.whatsapp-gateway')->with('success', 'Webhook WhatsApp berhasil disimulasikan.');
    }

    private function authorizeIntegration(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage modules') || $user?->hasPermission('view attendance'), 403);
    }
}
