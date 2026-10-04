<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->leftJoin('checklists', 'checklists.task_id', '=', 'tasks.id')
    ->leftJoin('checklist_items', 'checklist_items.checklist_id', '=', 'checklists.id')
    ->where('task_lists.board_id', 6)
    ->whereNull('tasks.archived_at')
    ->where(function ($query) {
        $query->where('tasks.title', 'like', '%psiko%')
            ->orWhere('tasks.description', 'like', '%psiko%')
            ->orWhere('checklist_items.name', 'like', '%psiko%')
            ->orWhere('tasks.title', 'like', '%psych%')
            ->orWhere('tasks.description', 'like', '%psych%')
            ->orWhere('checklist_items.name', 'like', '%psych%')
            ->orWhere('tasks.title', 'like', '%assessment%')
            ->orWhere('tasks.description', 'like', '%assessment%')
            ->orWhere('checklist_items.name', 'like', '%assessment%');
    })
    ->select('tasks.id', 'tasks.title', 'tasks.start_date', 'tasks.due_date', 'task_lists.name as list')
    ->distinct()
    ->orderBy('tasks.title')
    ->get();

echo json_encode([
    'matches' => $rows->count(),
    'cards' => $rows,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
