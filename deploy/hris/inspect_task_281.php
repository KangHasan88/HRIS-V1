<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = Task::with('taskList')->findOrFail(281);
echo $task->id . ' | ' . $task->taskList->name . ' | ' . $task->start_date . ' | ' . $task->due_date . ' | ' . $task->title . PHP_EOL;
echo $task->description . PHP_EOL;
