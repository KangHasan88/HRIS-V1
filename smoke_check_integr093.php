<?php

use App\Models\AttendanceImportBatch;
use App\Models\BiometricConnector;
use App\Models\BiometricSyncLog;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$batch = AttendanceImportBatch::with('rows')->where('batch_number', 'AGENT-CSV-SMOKE-093-20260721-0900')->firstOrFail();
$connector = BiometricConnector::where('connector_code', 'BIO-AGENT-SMOKE')->firstOrFail();
$log = BiometricSyncLog::where('attendance_import_batch_id', $batch->id)->latest()->firstOrFail();

echo json_encode([
    'batch' => $batch->batch_number,
    'source_type' => $batch->source_type,
    'imported' => $batch->imported_rows,
    'matched' => $batch->matched_rows,
    'anomaly' => $batch->anomaly_rows,
    'connector_agent_status' => $connector->agent_status,
    'agent_version' => $connector->agent_version,
    'sync_status' => $log->status,
    'rows' => $batch->rows->map(fn ($row) => [$row->raw_identifier, $row->match_status])->values(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
