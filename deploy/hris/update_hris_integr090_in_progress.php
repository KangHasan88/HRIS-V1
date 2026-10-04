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
    ->where('title', 'like', '[INTEGR-090]%')
    ->firstOrFail();

$inProgress = TaskList::where('board_id', $boardId)->where('name', 'In Progress')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $inProgress->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 15;
}
$task->save();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Mulai dikerjakan: INTEGR-090 Template ekspor pajak dan BPJS dengan validasi. Scope awal: halaman Export Pajak & BPJS, validasi profil PPh 21, validasi BPJS payroll, template CSV Coretax/BPJS, validation summary, preview row, endpoint download CSV, audit/test Laravel.',
    'mentions' => [],
    'likes_count' => 0,
]);

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo json_encode([
    'code' => 'INTEGR-090',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
], JSON_PRETTY_PRINT) . PHP_EOL;
