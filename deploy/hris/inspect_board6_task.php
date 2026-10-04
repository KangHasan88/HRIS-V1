<?php

use App\Models\Task;
use App\Models\WbsItem;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = (int) ($argv[1] ?? 0);
$task = Task::with(['taskList', 'comments', 'labels', 'checklists.items'])->findOrFail($id);
$wbs = WbsItem::where('board_id', 6)->where('task_id', $task->id)->first();

echo json_encode([
    'id' => $task->id,
    'list' => $task->taskList?->name,
    'title' => $task->title,
    'description' => $task->description,
    'progress' => $task->progress ?? null,
    'start_date' => $task->start_date ? substr((string) $task->start_date, 0, 10) : null,
    'due_date' => $task->due_date ? substr((string) $task->due_date, 0, 10) : null,
    'labels' => $task->labels->pluck('name')->all(),
    'checklists' => $task->checklists->map(fn ($checklist) => [
        'name' => $checklist->name,
        'items' => $checklist->items->map(fn ($item) => [
            'title' => $item->title,
            'done' => (bool) $item->is_completed,
        ])->all(),
    ])->all(),
    'wbs' => $wbs ? [
        'code' => $wbs->code ?? null,
        'name' => $wbs->name ?? null,
        'target' => $wbs->target ?? null,
        'status' => $wbs->status ?? null,
        'progress' => $wbs->progress ?? null,
        'start_date' => $wbs->start_date ?? null,
        'due_date' => $wbs->due_date ?? null,
    ] : null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
