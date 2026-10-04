<?php

use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$tasks = Task::with(['taskList', 'comments'])
    ->whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->get();

$listCounts = $tasks->groupBy(fn ($task) => $task->taskList?->name ?: '-')
    ->map->count()
    ->sortKeys();

$issues = [];

foreach ($tasks as $task) {
    $list = $task->taskList?->name ?: '-';
    $progress = (int) ($task->progress ?? 0);
    $comments = $task->comments->pluck('content')->implode("\n");
    $hasUpdateComment = preg_match('/Update progress|siap testing|completed|selesai|Laravel test|smoke/i', $comments) === 1;

    $checklistTotal = 0;
    $checklistDone = 0;
    foreach (['checklist_items', 'checklists', 'todos'] as $column) {
        if (Schema::hasColumn('tasks', $column) && is_array($task->{$column})) {
            foreach ($task->{$column} as $item) {
                $checklistTotal++;
                if (($item['checked'] ?? $item['done'] ?? $item['completed'] ?? false) === true) {
                    $checklistDone++;
                }
            }
        }
    }

    if (in_array($list, ['Backlog', 'Ready'], true) && ($progress > 0 || $hasUpdateComment || $checklistDone > 0)) {
        $issues[] = [
            'type' => 'should_not_be_backlog_or_ready',
            'id' => $task->id,
            'title' => $task->title,
            'list' => $list,
            'progress' => $progress,
            'checklist' => "{$checklistDone}/{$checklistTotal}",
            'has_update_comment' => $hasUpdateComment,
        ];
    }

    if (in_array($list, ['In Progress', 'Review', 'Testing'], true) && $progress === 0) {
        $issues[] = [
            'type' => 'active_list_with_zero_progress',
            'id' => $task->id,
            'title' => $task->title,
            'list' => $list,
            'progress' => $progress,
            'checklist' => "{$checklistDone}/{$checklistTotal}",
            'has_update_comment' => $hasUpdateComment,
        ];
    }

    if ($list === 'Testing' && $progress < 80) {
        $issues[] = [
            'type' => 'testing_low_progress',
            'id' => $task->id,
            'title' => $task->title,
            'list' => $list,
            'progress' => $progress,
            'checklist' => "{$checklistDone}/{$checklistTotal}",
            'has_update_comment' => $hasUpdateComment,
        ];
    }
}

echo json_encode([
    'board_id' => $boardId,
    'list_counts' => $listCounts,
    'issue_count' => count($issues),
    'issues' => $issues,
], JSON_PRETTY_PRINT) . PHP_EOL;
