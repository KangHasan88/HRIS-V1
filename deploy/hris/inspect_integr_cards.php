<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Task::where('title', 'like', '[INTEGR-%')
    ->orderBy('title')
    ->get(['id', 'title', 'due_date'])
    ->each(fn ($task) => print $task->id . ' | ' . $task->due_date . ' | ' . $task->title . PHP_EOL);
