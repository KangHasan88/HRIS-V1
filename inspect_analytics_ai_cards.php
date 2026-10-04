<?php

use App\Models\Task;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where(function ($query) {
        $query->where('title', 'like', '[ANALYT-%')
            ->orWhere('title', 'like', '[AI-%');
    })
    ->orderBy('title')
    ->get()
    ->each(function ($task) {
        echo implode('|', [
            $task->id,
            $task->taskList?->name,
            $task->progress ?? 0,
            $task->title,
        ]).PHP_EOL;
    });
