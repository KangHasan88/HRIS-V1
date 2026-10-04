<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where('title', 'like', '[EXTWF-099]%')
    ->firstOrFail();

$task->task_list_id = TaskList::where('board_id', 6)->where('name', 'In Progress')->firstOrFail()->id;
$task->progress = 10;
$task->save();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'In Progress 10%: mulai eksekusi EXTWF-099 untuk output borongan, master tarif pekerjaan, input output worker/team, approval mandor, dan handoff nilai ke invoice/payroll.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'task_id' => $task->id,
    'title' => $task->title,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress,
], JSON_PRETTY_PRINT).PHP_EOL;
