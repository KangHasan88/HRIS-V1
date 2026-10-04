<?php

use App\Models\Checklist;
use App\Models\Task;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = Task::with('taskList')->findOrFail(342);
$checklists = Checklist::where('task_id', $task->id)
    ->with('items')
    ->get()
    ->map(fn ($checklist) => [
        'title' => $checklist->title,
        'items' => $checklist->items->map(fn ($item) => [
            'content' => $item->content,
            'checked' => (bool) $item->is_checked,
        ])->values(),
    ]);

echo json_encode([
    'id' => $task->id,
    'title' => $task->title,
    'list' => $task->taskList->name,
    'description' => $task->description,
    'due' => $task->due_date?->format('Y-m-d'),
    'progress' => $task->progress,
    'checklists' => $checklists,
], JSON_PRETTY_PRINT).PHP_EOL;
