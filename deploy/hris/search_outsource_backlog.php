<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$keywords = ['vendor', 'outsour', 'borongan', 'harian', 'mandor', 'tenaga', 'worker', 'piece'];

$tasks = Task::whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where(function ($query) use ($keywords) {
        foreach ($keywords as $keyword) {
            $query->orWhere('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        }
    })
    ->with('taskList')
    ->orderBy('id')
    ->get();

foreach ($tasks as $task) {
    echo "{$task->id} | {$task->taskList->name} | {$task->title}\n";
}

echo "total={$tasks->count()}\n";
