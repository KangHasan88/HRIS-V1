<?php

use App\Models\Task;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$items = Task::whereHas('taskList', function ($query) {
    $query->where('board_id', 6)->whereIn('name', ['Ready', 'Backlog']);
})
    ->with('taskList')
    ->get()
    ->sortBy([
        fn ($a, $b) => ($a->taskList->name === 'Ready' ? 0 : 1) <=> ($b->taskList->name === 'Ready' ? 0 : 1),
        fn ($a, $b) => strcmp((string) $a->due_date, (string) $b->due_date),
        fn ($a, $b) => $a->id <=> $b->id,
    ])
    ->take(10)
    ->map(fn ($task) => [
        'id' => $task->id,
        'list' => $task->taskList->name,
        'title' => $task->title,
        'due' => $task->due_date?->format('Y-m-d'),
        'progress' => $task->progress,
    ])
    ->values();

echo $items->toJson(JSON_PRETTY_PRINT).PHP_EOL;
