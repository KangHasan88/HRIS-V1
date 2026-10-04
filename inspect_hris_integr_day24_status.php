<?php

use App\Models\Task;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where(fn ($query) => $query->where('title', 'like', '[DAY-24]%')->orWhere('title', 'like', '[INTEGR-%'))
    ->orderBy('title')
    ->get()
    ->map(fn ($task) => [
        'title' => $task->title,
        'list' => $task->taskList?->name,
        'progress' => $task->progress ?? null,
        'due_date' => $task->due_date ?? null,
    ])
    ->values();

echo json_encode($rows, JSON_PRETTY_PRINT).PHP_EOL;
