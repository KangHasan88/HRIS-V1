<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$items = Task::query()
    ->join('task_lists', 'tasks.task_list_id', '=', 'task_lists.id')
    ->where('task_lists.board_id', 6)
    ->whereIn('task_lists.name', ['In Progress', 'Ready'])
    ->whereNull('tasks.archived_at')
    ->orderByRaw("case task_lists.name when 'In Progress' then 0 else 1 end")
    ->orderBy('tasks.due_date')
    ->limit(12)
    ->get([
        'tasks.id',
        'tasks.title',
        'task_lists.name as list',
        'tasks.progress',
        'tasks.due_date',
    ]);

echo json_encode($items, JSON_PRETTY_PRINT) . PHP_EOL;
