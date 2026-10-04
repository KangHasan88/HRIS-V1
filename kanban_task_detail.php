<?php

use App\Models\Task;
use App\Models\WbsItem;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$code = $argv[1] ?? '';

$task = Task::with(['taskList', 'checklists.items', 'labels', 'comments'])
    ->whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where('title', 'like', '[' . $code . ']%')
    ->firstOrFail();

$wbs = WbsItem::where('board_id', 6)->where('task_id', $task->id)->first();

echo json_encode([
    'id' => $task->id,
    'title' => $task->title,
    'list' => $task->taskList?->name,
    'due_date' => $task->due_date,
    'progress' => $task->progress,
    'description' => $task->description,
    'labels' => $task->labels->pluck('name')->values(),
    'checklists' => $task->checklists->map(fn ($checklist) => [
        'title' => $checklist->title,
        'items' => $checklist->items->map(fn ($item) => [
            'title' => $item->title,
            'is_completed' => $item->is_completed,
        ])->values(),
    ])->values(),
    'wbs' => $wbs ? [
        'id' => $wbs->id,
        'code' => $wbs->code,
        'title' => $wbs->title,
        'status' => $wbs->status ?? null,
        'progress' => $wbs->progress ?? null,
        'start_date' => $wbs->start_date ?? null,
        'due_date' => $wbs->due_date ?? null,
    ] : null,
], JSON_PRETTY_PRINT) . PHP_EOL;
