<?php

use App\Models\Task;
use App\Models\TaskList;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$readyId = TaskList::where('board_id', $boardId)->where('name', 'Ready')->value('id');

Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', $boardId)->whereIn('name', ['Ready', 'Backlog']))
    ->orderByRaw('case when task_list_id = ? then 0 else 1 end', [$readyId])
    ->orderBy('due_date')
    ->limit(10)
    ->get()
    ->each(function ($task) {
        echo implode('|', [
            $task->id,
            $task->taskList->name,
            $task->due_date,
            $task->title,
        ]).PHP_EOL;
    });
