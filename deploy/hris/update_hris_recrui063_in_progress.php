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
    ->where('title', 'like', '[RECRUI-063]%')
    ->firstOrFail();

$inProgress = TaskList::where('board_id', $boardId)->where('name', 'In Progress')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $inProgress->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 15;
}
$task->save();

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
if ($wbs) {
    $wbsColumns = Schema::getColumnListing('wbs_items');
    if (in_array('progress', $wbsColumns, true)) {
        $wbs->progress = 15;
    }
    if (in_array('status', $wbsColumns, true)) {
        $wbs->status = 'In Progress';
    }
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Mulai dikerjakan: RECRUI-063 Pembuatan offer letter dan tracking tanda tangan elektronik. Scope awal: tabel offer letter, template offer, status draft/sent/signed/declined/expired, tracking provider tanda tangan elektronik, route/menu Talent Acquisition, audit log, dan test Laravel.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'RECRUI-063',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
], JSON_PRETTY_PRINT) . PHP_EOL;
