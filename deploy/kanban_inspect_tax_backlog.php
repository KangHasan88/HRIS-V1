<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where('title', 'like', '[TAX-%')
    ->orderBy('id')
    ->get()
    ->each(function (Task $task) {
        echo implode(' | ', [
            $task->id,
            $task->taskList?->name,
            ($task->progress ?? 0) . '%',
            $task->start_date ?: '-',
            $task->due_date ?: '-',
            $task->title,
        ]) . PHP_EOL;
    });
