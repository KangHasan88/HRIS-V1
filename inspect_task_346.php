<?php

use App\Models\Task;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = Task::with(['taskList', 'checklists.items', 'comments'])->findOrFail(346);

echo json_encode([
    'id' => $task->id,
    'title' => $task->title,
    'list' => $task->taskList?->name,
    'description' => $task->description,
    'progress' => $task->progress ?? null,
    'due_date' => $task->due_date ?? null,
    'checklists' => $task->checklists->map(fn ($checklist) => [
        'title' => $checklist->title,
        'items' => $checklist->items->map(fn ($item) => [
            'text' => $item->content ?? $item->title ?? $item->name ?? null,
            'checked' => (bool) $item->is_checked,
        ]),
    ]),
], JSON_PRETTY_PRINT).PHP_EOL;
