<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tasks = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->whereHas('taskList', fn ($query) => $query->whereIn('name', ['Backlog', 'Ready']))
    ->orderByRaw("FIELD(task_list_id, (select id from task_lists where board_id = 6 and name = 'Ready' limit 1), (select id from task_lists where board_id = 6 and name = 'Backlog' limit 1))")
    ->orderBy('start_date')
    ->orderBy('id')
    ->limit(40)
    ->get();

foreach ($tasks as $task) {
    echo implode(' | ', [
        $task->id,
        $task->taskList?->name,
        ($task->progress ?? 0) . '%',
        $task->priority ?? '-',
        $task->start_date ? substr((string) $task->start_date, 0, 10) : '-',
        $task->due_date ? substr((string) $task->due_date, 0, 10) : '-',
        $task->title,
    ]) . PHP_EOL;
}
