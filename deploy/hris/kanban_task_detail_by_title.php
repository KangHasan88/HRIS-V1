<?php

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Task;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$prefix = $argv[1] ?? '';
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where('title', 'like', $prefix.'%')
    ->with('taskList')
    ->firstOrFail();

echo json_encode([
    'id' => $task->id,
    'list' => $task->taskList?->name,
    'title' => $task->title,
    'due' => optional($task->due_date)->toDateString(),
    'progress' => $task->progress,
    'description' => $task->description,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;

$checklistIds = Checklist::where('task_id', $task->id)->pluck('id');
ChecklistItem::whereIn('checklist_id', $checklistIds)
    ->orderBy('position')
    ->get()
    ->each(fn ($item) => print(json_encode([
        'checked' => (bool) $item->is_checked,
        'content' => $item->content,
    ], JSON_UNESCAPED_UNICODE).PHP_EOL));
