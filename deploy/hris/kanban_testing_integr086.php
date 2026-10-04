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
    ->where('title', 'like', '[INTEGR-086]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
if ($wbs) {
    $columns = Schema::getColumnListing('wbs_items');
    if (in_array('status', $columns, true)) {
        $wbs->status = 'Testing';
    }
    if (in_array('progress', $columns, true)) {
        $wbs->progress = 90;
    }
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: INTEGR-086 WhatsApp Gateway siap Testing. Implementasi mencakup tabel whatsapp_gateway_settings, whatsapp_message_queues, whatsapp_webhook_logs, model setting/queue/webhook, controller konfigurasi gateway, queue pesan approval/reminder/slip gaji/manual, simulasi webhook status sent/delivered/read/failed, halaman admin Integrasi > WhatsApp Gateway, menu sidebar, audit log gateway/message/webhook, dan smoke live admin. Full Laravel test 88 passed / 1107 assertions. Smoke HTTPS 8091 pass: GET gateway 200, create gateway 302, queue message 302, final page 200 dengan data muncul.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'task_id' => $task->id,
    'title' => $task->title,
    'list' => $testing->name,
    'progress' => $task->progress ?? null,
    'wbs_id' => $wbs?->id,
], JSON_PRETTY_PRINT) . PHP_EOL;
