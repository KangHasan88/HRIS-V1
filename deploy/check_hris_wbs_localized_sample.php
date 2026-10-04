<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = DB::table('wbs_items')
    ->join('tasks', 'tasks.id', '=', 'wbs_items.task_id')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('wbs_items.board_id', 6)
    ->whereIn('tasks.id', [202, 203, 204, 205, 206])
    ->select(
        'wbs_items.code',
        'wbs_items.title as wbs_title',
        'wbs_items.target',
        'tasks.title as card_title',
        'task_lists.name as status',
        'tasks.start_date',
        'tasks.due_date'
    )
    ->orderBy('tasks.id')
    ->get();

echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
