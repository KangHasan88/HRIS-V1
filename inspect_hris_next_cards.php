<?php

use App\Models\Task;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$order = ['In Progress' => 1, 'Ready' => 2, 'Backlog' => 3];

$rows = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', 6)->whereIn('name', array_keys($order)))
    ->get()
    ->sortBy(fn ($task) => sprintf(
        '%02d-%s-%05d',
        $order[$task->taskList?->name] ?? 9,
        optional($task->due_date)->format('Y-m-d') ?? '9999-12-31',
        $task->id
    ))
    ->take(25)
    ->map(fn ($task) => [
        'id' => $task->id,
        'title' => $task->title,
        'list' => $task->taskList?->name,
        'progress' => $task->progress ?? null,
        'due_date' => optional($task->due_date)->format('Y-m-d'),
    ])
    ->values();

echo json_encode($rows, JSON_PRETTY_PRINT).PHP_EOL;
