<?php

use App\Models\Task;
use App\Models\TaskList;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$readyId = TaskList::where('board_id', 6)->where('name', 'Ready')->value('id');

Task::whereHas('taskList', fn ($query) => $query->where('board_id', 6)->whereIn('name', ['Ready', 'Backlog']))
    ->with('taskList')
    ->orderByRaw('case when task_list_id = ? then 0 else 1 end', [$readyId])
    ->orderBy('due_date')
    ->limit(10)
    ->get(['id', 'title', 'task_list_id', 'due_date', 'progress'])
    ->each(function (Task $task) {
        echo json_encode([
            'id' => $task->id,
            'list' => $task->taskList?->name,
            'title' => $task->title,
            'due' => optional($task->due_date)->toDateString(),
            'progress' => $task->progress,
        ], JSON_UNESCAPED_UNICODE).PHP_EOL;
    });
