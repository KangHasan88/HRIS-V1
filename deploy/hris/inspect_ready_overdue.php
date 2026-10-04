<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$today = now()->toDateString();

$tasks = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', $boardId)->where('name', 'Ready'))
    ->orderBy('due_date')
    ->orderBy('start_date')
    ->orderBy('id')
    ->get();

foreach ($tasks as $task) {
    $due = $task->due_date ? substr((string) $task->due_date, 0, 10) : '-';
    $start = $task->start_date ? substr((string) $task->start_date, 0, 10) : '-';
    $overdue = $due !== '-' && $due < $today ? 'OVERDUE' : 'OK';
    echo implode(' | ', [
        $task->id,
        $task->taskList?->name,
        ($task->progress ?? 0) . '%',
        $start,
        $due,
        $overdue,
        $task->title,
    ]) . PHP_EOL;
}
