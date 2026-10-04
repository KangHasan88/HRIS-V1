<?php

use App\Models\Task;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach ([331, 332, 333, 334, 335] as $id) {
    $task = Task::with('taskList')->find($id);
    if (!$task) {
        continue;
    }

    echo $task->id . ' | ' . $task->taskList->name . ' | ' . $task->due_date . ' | ' . $task->title . PHP_EOL;
    echo mb_substr((string) $task->description, 0, 900) . PHP_EOL;
    echo '---' . PHP_EOL;
}
