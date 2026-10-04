<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[TAX-010]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: TAX-010 Kesiapan connector API Coretax dengan credential vault dan retry log siap testing. Sudah dibuat tabel coretax_connector_settings dan coretax_retry_logs, model CoretaxConnectorSetting/CoretaxRetryLog, controller, halaman Coretax Connector, route GET payroll/coretax-connector, POST settings, POST queue, menu Payroll & Tax, credential vault dengan secret masking + hash, environment sandbox/production, timeout/max retry, queue template Coretax, endpoint snapshot, payload checksum, next retry, request/response snapshot, dan audit log coretax_connector.setting_created + coretax_connector.retry_queued. Migration sukses, Laravel test 24 passed / 227 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'TAX-010',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
