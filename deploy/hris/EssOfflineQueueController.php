<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EssOfflineQueueItem;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EssOfflineQueueController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view attendance');

        $items = EssOfflineQueueItem::with('employeeProfile')
            ->latest('captured_at')
            ->latest()
            ->limit(150)
            ->get();

        return view('ess.offline-queue', [
            'active' => 'ess-offline-queue',
            'employees' => EmployeeProfile::where('is_active', true)->orderBy('employee_number')->get(),
            'items' => $items,
            'summary' => [
                'queued' => $items->where('queue_status', 'queued')->count(),
                'synced' => $items->where('queue_status', 'synced')->count(),
                'failed' => $items->where('queue_status', 'failed')->count(),
                'clock' => $items->where('item_type', 'clock_event')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('view attendance');

        $validated = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'queue_number' => ['required', 'string', 'max:80', 'unique:ess_offline_queue_items,queue_number'],
            'item_type' => ['required', Rule::in(['clock_event', 'leave_draft', 'overtime_draft', 'business_trip_draft', 'attendance_correction_draft'])],
            'queue_status' => ['required', Rule::in(['queued', 'syncing', 'synced', 'failed', 'cancelled'])],
            'captured_at' => ['required', 'date'],
            'device_uid' => ['nullable', 'string', 'max:160'],
            'device_name' => ['nullable', 'string', 'max:160'],
            'platform' => ['required', Rule::in(['mobile_webview', 'android_webview', 'ios_webview', 'browser'])],
            'app_version' => ['nullable', 'string', 'max:40'],
            'payload_key' => ['nullable', 'string', 'max:120'],
            'payload_value' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1200'],
            'failure_reason' => ['nullable', 'string', 'max:1200'],
        ]);

        $payload = [
            'source' => 'ESS Offline Queue',
            'payload_key' => $validated['payload_key'] ?? null,
            'payload_value' => $validated['payload_value'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        $item = EssOfflineQueueItem::create([
            ...collect($validated)->except(['payload_key', 'payload_value', 'notes'])->all(),
            'payload' => $payload,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'ess_offline_queue.created',
            "Offline queue {$item->queue_number} dibuat untuk {$item->employeeProfile?->employee_number}.",
            $item,
            [],
            $item->toArray(),
            $request,
        );

        return redirect()->route('ess.offline-queue')->with('success', 'Offline queue berhasil disimpan.');
    }

    public function sync(EssOfflineQueueItem $offlineQueueItem, Request $request): RedirectResponse
    {
        Gate::authorize('view attendance');

        $before = $offlineQueueItem->toArray();
        $offlineQueueItem->forceFill([
            'queue_status' => 'synced',
            'last_sync_attempt_at' => now(),
            'synced_at' => now(),
            'retry_count' => $offlineQueueItem->retry_count + 1,
            'sync_result' => [
                'result' => 'accepted',
                'processed_by' => auth()->user()?->email,
                'synced_from' => 'manual_sync',
            ],
            'failure_reason' => null,
        ])->save();

        AuditLogger::record(
            'ess_offline_queue.synced',
            "Offline queue {$offlineQueueItem->queue_number} berhasil disinkronkan.",
            $offlineQueueItem,
            $before,
            $offlineQueueItem->toArray(),
            $request,
        );

        return redirect()->route('ess.offline-queue')->with('success', 'Offline queue berhasil disinkronkan.');
    }

    public function fail(EssOfflineQueueItem $offlineQueueItem, Request $request): RedirectResponse
    {
        Gate::authorize('view attendance');

        $validated = $request->validate([
            'failure_reason' => ['required', 'string', 'max:1200'],
        ]);

        $before = $offlineQueueItem->toArray();
        $offlineQueueItem->forceFill([
            'queue_status' => 'failed',
            'last_sync_attempt_at' => now(),
            'retry_count' => $offlineQueueItem->retry_count + 1,
            'failure_reason' => $validated['failure_reason'],
            'sync_result' => [
                'result' => 'rejected',
                'processed_by' => auth()->user()?->email,
            ],
        ])->save();

        AuditLogger::record(
            'ess_offline_queue.failed',
            "Offline queue {$offlineQueueItem->queue_number} gagal disinkronkan.",
            $offlineQueueItem,
            $before,
            $offlineQueueItem->toArray(),
            $request,
        );

        return redirect()->route('ess.offline-queue')->with('success', 'Offline queue ditandai gagal.');
    }
}
