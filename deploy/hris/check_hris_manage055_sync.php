<?php

use App\Models\Task;
use App\Models\WbsItem;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where('title', 'like', '[MANAGE-055]%')
    ->firstOrFail();

$wbs = WbsItem::where('board_id', 6)->where('task_id', $task->id)->firstOrFail();

echo json_encode([
    'task_id' => $task->id,
    'card' => $task->title,
    'list' => $task->taskList?->name,
    'progress' => $task->progress ?? null,
    'start_date' => $task->start_date ?? null,
    'due_date' => $task->due_date ?? null,
    'wbs_code' => $wbs->code,
    'wbs_title' => $wbs->title,
    'wbs_card_match' => $wbs->task_id === $task->id,
], JSON_PRETTY_PRINT) . PHP_EOL;
