<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->join('boards', 'boards.id', '=', 'task_lists.board_id')
    ->where('boards.id', 6)
    ->where('tasks.title', 'like', '[TAX-%')
    ->whereNull('tasks.archived_at')
    ->orderBy('tasks.title')
    ->get(['tasks.id', 'tasks.title', 'task_lists.name as list']);

$checklistCount = DB::table('checklists')
    ->join('tasks', 'tasks.id', '=', 'checklists.task_id')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', 6)
    ->where('tasks.title', 'like', '[TAX-%')
    ->count();

$itemCount = DB::table('checklist_items')
    ->join('checklists', 'checklists.id', '=', 'checklist_items.checklist_id')
    ->join('tasks', 'tasks.id', '=', 'checklists.task_id')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', 6)
    ->where('tasks.title', 'like', '[TAX-%')
    ->count();

echo json_encode([
    'tax_cards' => $rows->count(),
    'checklists' => $checklistCount,
    'items' => $itemCount,
    'sample' => $rows->take(6),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
