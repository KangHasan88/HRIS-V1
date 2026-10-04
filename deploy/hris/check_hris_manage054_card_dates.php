<?php

use App\Models\Task;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where('title', 'like', '[MANAGE-054]%')
    ->firstOrFail();

$columns = Schema::getColumnListing('tasks');
$dateColumns = array_values(array_filter($columns, fn ($column) => str_contains($column, 'date') || str_contains($column, 'due') || str_contains($column, 'start')));

echo json_encode([
    'task_id' => $task->id,
    'list' => $task->taskList?->name,
    'progress' => $task->progress ?? null,
    'date_columns' => array_intersect_key($task->getAttributes(), array_flip($dateColumns)),
], JSON_PRETTY_PRINT) . PHP_EOL;
