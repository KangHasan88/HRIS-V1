<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', 6)->where('name', 'Backlog'))
    ->orderBy('due_date')
    ->orderBy('id')
    ->limit(12)
    ->get(['id', 'title', 'due_date', 'progress', 'task_list_id'])
    ->each(function (Task $task): void {
        echo implode(' | ', [
            $task->id,
            $task->due_date,
            $task->progress,
            $task->title,
        ]) . PHP_EOL;
    });
