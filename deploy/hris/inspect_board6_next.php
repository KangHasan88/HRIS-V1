<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$listColumns = Illuminate\Support\Facades\Schema::getColumnListing('task_lists');
$taskColumns = Illuminate\Support\Facades\Schema::getColumnListing('tasks');

$listOrder = in_array('position', $listColumns, true) ? 'position' : (in_array('order', $listColumns, true) ? 'order' : 'id');
$taskOrder = in_array('position', $taskColumns, true) ? 'position' : (in_array('order', $taskColumns, true) ? 'order' : 'id');

$lists = App\Models\TaskList::where('board_id', 6)->orderBy($listOrder)->get();

foreach ($lists as $list) {
    echo "LIST {$list->name}\n";

    foreach ($list->tasks()->orderBy($taskOrder)->limit(10)->get() as $task) {
        $due = $task->due_date ? $task->due_date->format('Y-m-d') : '-';
        $progress = $task->progress ?? 0;
        echo "{$task->id} | {$task->title} | due={$due} | progress={$progress}\n";
    }

    echo "\n";
}
