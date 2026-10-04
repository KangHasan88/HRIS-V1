<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', 6)
    ->where('tasks.title', 'like', '[MOBILE-%')
    ->whereNull('tasks.archived_at')
    ->orderBy('tasks.title')
    ->get(['tasks.id', 'tasks.title', 'task_lists.name as list']);

$noted = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', 6)
    ->where('tasks.description', 'like', '%Mobile Architecture Decision:%')
    ->whereNull('tasks.archived_at')
    ->count();

$webviewChecklist = DB::table('checklists')
    ->join('tasks', 'tasks.id', '=', 'checklists.task_id')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', 6)
    ->where('checklists.name', 'Mobile WebView Requirements')
    ->count();

echo json_encode([
    'mobile_webview_cards' => $rows->count(),
    'cards_with_mobile_architecture_note' => $noted,
    'mobile_webview_requirement_checklists' => $webviewChecklist,
    'sample' => $rows,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
