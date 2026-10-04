<?php

use App\Models\Task;
use App\Models\TaskList;
use App\Models\WbsItem;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$lists = TaskList::where('board_id', $boardId)
    ->withCount('allTasks')
    ->orderBy('order')
    ->get(['id', 'name', 'board_id', 'order']);

echo "LISTS\n";
foreach ($lists as $list) {
    echo "{$list->id} | {$list->name} | {$list->all_tasks_count}\n";
}

echo "\nTAX TASKS\n";
$tasks = Task::whereIn('task_list_id', $lists->pluck('id'))
    ->where(function ($query) {
        $query->where('title', 'like', '%TAX%')
            ->orWhere('title', 'like', '%PPh%')
            ->orWhere('title', 'like', '%Coretax%')
            ->orWhere('title', 'like', '%pajak%')
            ->orWhere('description', 'like', '%PPh%')
            ->orWhere('description', 'like', '%Coretax%')
            ->orWhere('description', 'like', '%pajak%');
    })
    ->with('taskList')
    ->orderBy('id')
    ->get();

foreach ($tasks as $task) {
    $wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code') ?: '-';
    $progress = $task->progress ?? '-';
    echo "{$task->id} | {$task->title} | {$task->taskList->name} | progress {$progress} | WBS {$wbs}\n";
}

