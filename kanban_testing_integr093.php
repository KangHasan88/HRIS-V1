<?php

use App\Models\Board;
use App\Models\Task;
use App\Models\TaskActivity;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$board = Board::query()->findOrFail(6);
$testing = $board->lists()->where('name', 'Testing')->firstOrFail();
$task = Task::with('checklists.items')->findOrFail(387);
$task->task_list_id = $testing->id;
$task->progress = 90;
$task->save();

foreach ($task->checklists as $checklist) {
    foreach ($checklist->items as $item) {
        $item->is_checked = true;
        $item->save();
    }
}

TaskActivity::query()->create([
    'task_id' => $task->id,
    'user_id' => $board->user_id,
    'action' => 'commented',
    'old_value' => null,
    'new_value' => 'Testing 90%: INTEGR-093 Local Attendance Agent MVP selesai. Sudah dibuat API /api/attendance-agent/clock-events, field pairing/token/status agent di Biometric Connector, data push agent masuk Attendance Import Batch + Rows + Biometric Sync Log, skeleton Windows CSV watcher dengan retry folder, dan UI connector menampilkan endpoint/pairing/agent status. Smoke HTTPS live OK: batch AGENT-CSV-SMOKE-093-20260721-0900, 2 rows, 1 matched, 1 needs_review, sync partial_success. Full Laravel test OK: 89 passed / 1127 assertions.',
]);

echo json_encode([
    'task' => $task->title,
    'list_id' => $task->task_list_id,
    'progress' => $task->progress,
    'checked' => $task->checklists->flatMap->items->where('is_checked', true)->count(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
