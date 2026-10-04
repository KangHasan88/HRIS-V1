<?php

use App\Models\Task;
use App\Models\TaskList;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$backlogId = TaskList::where('board_id', $boardId)->where('name', 'Backlog')->value('id');

Task::with('taskList')
    ->where('task_list_id', $backlogId)
    ->orderBy('start_date')
    ->orderBy('id')
    ->limit(15)
    ->get(['id', 'task_list_id', 'title', 'progress', 'start_date', 'due_date'])
    ->each(function (Task $task): void {
        echo json_encode([
            'id' => $task->id,
            'list' => $task->taskList?->name,
            'title' => $task->title,
            'progress' => $task->progress,
            'start' => $task->start_date ? substr((string) $task->start_date, 0, 10) : null,
            'due' => $task->due_date ? substr((string) $task->due_date, 0, 10) : null,
        ], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    });
