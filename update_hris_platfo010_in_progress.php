<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[PLATFO-010]%')
    ->firstOrFail();

$inProgress = TaskList::where('board_id', $boardId)->where('name', 'In Progress')->firstOrFail();

$task->task_list_id = $inProgress->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 15;
}
$task->save();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Mulai coding PLATFO-010: kontrol privasi data untuk masking field sensitif, consent, retensi data, policy per modul/role, preview dampak, audit log, route/menu admin, feature test, smoke HTTPS, dan sinkron Kanban/WBS/Gantt.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'PLATFO-010',
    'task_id' => $task->id,
    'title' => Str::limit($task->title, 100),
    'list' => $inProgress->name,
    'progress' => $task->progress ?? null,
], JSON_PRETTY_PRINT).PHP_EOL;
