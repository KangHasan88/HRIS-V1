<?php

use App\Models\Task;
use App\Models\TaskList;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$readyId = TaskList::where('board_id', $boardId)->where('name', 'Ready')->value('id');

$tasks = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', $boardId)->whereIn('name', ['Ready', 'Backlog']))
    ->whereNull('archived_at')
    ->orderByRaw('case when task_list_id = ? then 0 else 1 end', [$readyId])
    ->orderBy('due_date')
    ->orderBy('id')
    ->limit(12)
    ->get(['id', 'title', 'task_list_id', 'due_date', 'progress']);

foreach ($tasks as $task) {
    echo implode(' | ', [
        $task->id,
        $task->taskList->name,
        $task->title,
        'due=' . ($task->due_date ?: '-'),
        'progress=' . ($task->progress ?? 0),
    ]) . PHP_EOL;
}
