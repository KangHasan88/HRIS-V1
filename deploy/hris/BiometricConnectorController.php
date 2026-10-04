<?php

namespace App\Http\Controllers;

use App\Models\AttendanceImportBatch;
use App\Models\BiometricConnector;
use App\Models\BiometricSyncLog;
use App\Models\Branch;
use App\Models\Company;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BiometricConnectorController extends Controller
{
    public function index(): View
    {
        $this->authorizeConnector();

        $connectors = BiometricConnector::with(['company', 'branch'])
            ->withCount('syncLogs')
            ->latest()
            ->limit(100)
            ->get();

        $logs = BiometricSyncLog::with(['connector', 'batch'])
            ->latest('started_at')
            ->limit(120)
            ->get();

        $onlineAgents = $connectors->filter(function (BiometricConnector $connector) {
            if ($connector->connection_type !== 'local_agent' || ! $connector->agent_last_seen_at) {
                return false;
            }

            $thresholdMinutes = max((int) $connector->sync_interval_minutes * 2, 10);

            return $connector->agent_last_seen_at->gte(now()->subMinutes($thresholdMinutes))
                && in_array($connector->agent_status, ['online', 'idle', 'syncing', 'synced_with_anomaly'], true);
        });

        return view('integrations.biometric-connectors', [
            'active' => 'biometric-connectors',
            'companies' => Company::orderBy('code')->get(),
            'branches' => Branch::with('company')->orderBy('code')->get(),
            'connectors' => $connectors,
            'logs' => $logs,
            'summary' => [
                'connectors' => $connectors->count(),
                'active' => $connectors->where('status', 'active')->count(),
                'sync_success' => $logs->where('status', 'success')->count(),
                'failed_rows' => $logs->sum('failed_rows'),
                'agent_online' => $onlineAgents->count(),
                'agent_attention' => $connectors->where('connection_type', 'local_agent')->count() - $onlineAgents->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeConnector();

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'connector_code' => ['required', 'string', 'max:80', 'unique:biometric_connectors,connector_code'],
            'connector_name' => ['required', 'string', 'max:255'],
            'vendor' => ['required', Rule::in(['solution', 'fingertec', 'zkteco', 'suprema', 'attendance_management', 'generic'])],
            'connection_type' => ['required', Rule::in(['api_pull', 'webhook', 'sftp_csv', 'local_agent', 'local_db'])],
            'endpoint_url' => ['nullable', 'string', 'max:255'],
            'auth_mode' => ['required', Rule::in(['api_key', 'basic_auth', 'bearer_token', 'sftp_key', 'local_agent', 'db_credentials'])],
            'agent_token' => ['nullable', 'string', 'min:8', 'max:255'],
            'agent_config_note' => ['nullable', 'string', 'max:255'],
            'db_driver' => ['nullable', Rule::in(['mysql', 'sqlserver', 'postgresql', 'access_odbc', 'sqlite', 'odbc'])],
            'db_host' => ['nullable', 'string', 'max:160'],
            'db_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'db_name' => ['nullable', 'string', 'max:160'],
            'db_table' => ['nullable', 'string', 'max:160'],
            'db_username' => ['nullable', 'string', 'max:160'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'db_query_template' => ['nullable', 'string', 'max:2000'],
            'db_employee_column' => ['nullable', 'string', 'max:80'],
            'db_time_column' => ['nullable', 'string', 'max:80'],
            'db_event_column' => ['nullable', 'string', 'max:80'],
            'db_device_column' => ['nullable', 'string', 'max:80'],
            'db_increment_column' => ['nullable', 'string', 'max:80'],
            'db_timezone' => ['nullable', 'string', 'max:80'],
            'db_poll_mode' => ['nullable', Rule::in(['timestamp_window', 'incremental_id', 'full_scan_preview'])],
            'machine_code' => ['nullable', 'string', 'max:80'],
            'machine_location' => ['nullable', 'string', 'max:255'],
            'sync_interval_minutes' => ['required', 'integer', 'min:5', 'max:10080'],
            'status' => ['required', Rule::in(['draft', 'active', 'paused', 'error'])],
            'mapping_employee_field' => ['required', Rule::in(['employee_number', 'nik', 'raw_identifier'])],
            'mapping_time_field' => ['required', 'string', 'max:80'],
            'mapping_event_field' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $connector = BiometricConnector::create([
            ...collect($validated)->except([
                'mapping_employee_field',
                'mapping_time_field',
                'mapping_event_field',
                'agent_token',
                'agent_config_note',
                'db_driver',
                'db_host',
                'db_port',
                'db_name',
                'db_table',
                'db_username',
                'db_password',
                'db_query_template',
                'db_employee_column',
                'db_time_column',
                'db_event_column',
                'db_device_column',
                'db_increment_column',
                'db_timezone',
                'db_poll_mode',
            ])->all(),
            'agent_pairing_code' => in_array($validated['connection_type'], ['local_agent', 'local_db'], true) ? Str::upper($validated['connector_code'] . '-PAIR') : null,
            'agent_token_hash' => ! empty($validated['agent_token']) ? Hash::make($validated['agent_token']) : null,
            'agent_status' => in_array($validated['connection_type'], ['local_agent', 'local_db'], true) ? 'waiting_first_sync' : 'unpaired',
            'next_sync_at' => $validated['status'] === 'active' ? now()->addMinutes((int) $validated['sync_interval_minutes']) : null,
            'mapping_config' => [
                'employee_field' => $validated['mapping_employee_field'],
                'time_field' => $validated['mapping_time_field'],
                'event_field' => $validated['mapping_event_field'],
            ],
            'agent_config' => in_array($validated['connection_type'], ['local_agent', 'local_db'], true) ? [
                'api_endpoint' => url('/api/attendance-agent/clock-events'),
                'heartbeat_endpoint' => url('/api/attendance-agent/heartbeat'),
                'source_mode' => $validated['connection_type'] === 'local_db' ? 'local_database' : 'csv_folder',
                'note' => $validated['agent_config_note'] ?? null,
            ] : null,
            'database_adapter_config' => $validated['connection_type'] === 'local_db' ? [
                'driver' => $validated['db_driver'] ?? 'mysql',
                'host' => $validated['db_host'] ?? null,
                'port' => $validated['db_port'] ?? null,
                'database' => $validated['db_name'] ?? null,
                'table' => $validated['db_table'] ?? null,
                'username' => $validated['db_username'] ?? null,
                'password_saved' => filled($validated['db_password'] ?? null),
                'query_template' => $validated['db_query_template'] ?? null,
                'employee_column' => $validated['db_employee_column'] ?? 'employee_number',
                'time_column' => $validated['db_time_column'] ?? 'event_time',
                'event_column' => $validated['db_event_column'] ?? 'event_type',
                'device_column' => $validated['db_device_column'] ?? 'device_id',
                'increment_column' => $validated['db_increment_column'] ?? null,
                'timezone' => $validated['db_timezone'] ?? 'Asia/Jakarta',
                'poll_mode' => $validated['db_poll_mode'] ?? 'timestamp_window',
            ] : null,
            'adapter_status' => $validated['connection_type'] === 'local_db' ? 'configured' : 'not_configured',
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'biometric_connector.created',
            "Connector biometric {$connector->connector_code} dibuat.",
            $connector,
            [],
            $connector->toArray(),
            $request,
        );

        return redirect()->route('integrations.biometric-connectors')->with('success', 'Connector biometric berhasil dibuat.');
    }

    public function testAdapter(Request $request, BiometricConnector $connector): RedirectResponse
    {
        $this->authorizeConnector();

        abort_unless($connector->connection_type === 'local_db', 404);

        $validated = $request->validate([
            'preview_rows' => ['required', 'integer', 'min:0', 'max:5000'],
            'matched_rows' => ['required', 'integer', 'min:0', 'max:5000'],
            'failed_rows' => ['required', 'integer', 'min:0', 'max:5000'],
            'sample_employee' => ['nullable', 'string', 'max:80'],
            'sample_time' => ['nullable', 'date'],
            'sample_event' => ['nullable', 'string', 'max:80'],
        ]);

        $log = BiometricSyncLog::create([
            'biometric_connector_id' => $connector->id,
            'sync_number' => 'ADAPTER-TEST-' . now()->format('YmdHis') . '-' . $connector->id,
            'started_at' => now()->subSeconds(5),
            'finished_at' => now(),
            'status' => (int) $validated['failed_rows'] > 0 ? 'partial_success' : 'success',
            'pulled_rows' => $validated['preview_rows'],
            'matched_rows' => $validated['matched_rows'],
            'failed_rows' => $validated['failed_rows'],
            'payload_summary' => [
                'adapter' => $connector->database_adapter_config,
                'sample' => [
                    'employee' => $validated['sample_employee'] ?? null,
                    'time' => $validated['sample_time'] ?? null,
                    'event' => $validated['sample_event'] ?? null,
                ],
            ],
            'message' => 'Preview adapter database lokal dicatat. Eksekusi koneksi real dilakukan oleh Windows agent di jaringan lokal.',
            'created_by' => auth()->id(),
        ]);

        $connector->forceFill([
            'adapter_status' => (int) $validated['failed_rows'] > 0 ? 'needs_mapping_review' : 'preview_ok',
            'adapter_last_test_at' => now(),
            'adapter_last_preview' => [
                'preview_rows' => $validated['preview_rows'],
                'matched_rows' => $validated['matched_rows'],
                'failed_rows' => $validated['failed_rows'],
                'sample_employee' => $validated['sample_employee'] ?? null,
                'sample_time' => $validated['sample_time'] ?? null,
                'sample_event' => $validated['sample_event'] ?? null,
            ],
        ])->save();

        AuditLogger::record(
            'biometric_connector.adapter_tested',
            "Adapter database {$connector->connector_code} dites.",
            $log,
            [],
            $log->toArray(),
            $request,
        );

        return redirect()->route('integrations.biometric-connectors')->with('success', 'Preview adapter database berhasil dicatat.');
    }

    public function sync(Request $request, BiometricConnector $connector): RedirectResponse
    {
        $this->authorizeConnector();

        $validated = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'pulled_rows' => ['required', 'integer', 'min:0', 'max:100000'],
            'matched_rows' => ['required', 'integer', 'min:0', 'max:100000'],
            'failed_rows' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $batch = AttendanceImportBatch::create([
            'batch_number' => 'BIO-' . now()->format('YmdHis') . '-' . $connector->id,
            'company_id' => $connector->company_id,
            'branch_id' => $connector->branch_id,
            'source_type' => 'api_sync',
            'machine_code' => $connector->machine_code ?: $connector->connector_code,
            'machine_name' => $connector->connector_name,
            'import_period_start' => $validated['period_start'],
            'import_period_end' => $validated['period_end'],
            'file_reference' => $connector->endpoint_url ?: $connector->connection_type,
            'imported_rows' => $validated['pulled_rows'],
            'matched_rows' => $validated['matched_rows'],
            'anomaly_rows' => $validated['failed_rows'],
            'status' => (int) $validated['failed_rows'] > 0 ? 'validated' : 'reconciled',
            'notes' => 'Generated by biometric connector sync ' . $connector->connector_code,
            'created_by' => auth()->id(),
        ]);

        $log = BiometricSyncLog::create([
            'biometric_connector_id' => $connector->id,
            'attendance_import_batch_id' => $batch->id,
            'sync_number' => 'SYNC-' . now()->format('YmdHis') . '-' . $connector->id,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'status' => (int) $validated['failed_rows'] > 0 ? 'partial_success' : 'success',
            'pulled_rows' => $validated['pulled_rows'],
            'matched_rows' => $validated['matched_rows'],
            'failed_rows' => $validated['failed_rows'],
            'payload_summary' => [
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'mapping' => $connector->mapping_config,
                'connection_type' => $connector->connection_type,
            ],
            'message' => 'Simulasi sync connector berhasil diproses ke batch import absensi.',
            'created_by' => auth()->id(),
        ]);

        $connector->forceFill([
            'last_sync_at' => $log->finished_at,
            'next_sync_at' => $connector->status === 'active' ? now()->addMinutes((int) $connector->sync_interval_minutes) : null,
            'status' => (int) $validated['failed_rows'] > 0 ? 'error' : $connector->status,
        ])->save();

        AuditLogger::record(
            'biometric_connector.synced',
            "Connector biometric {$connector->connector_code} disinkron.",
            $log,
            [],
            $log->toArray(),
            $request,
        );

        return redirect()->route('integrations.biometric-connectors')->with('success', 'Sync connector biometric berhasil diproses.');
    }

    private function authorizeConnector(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('manage attendance imports') || $user?->hasPermission('manage modules'), 403);
    }
}
