<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', 6)
    ->whereNull('tasks.archived_at')
    ->select('tasks.id', 'tasks.title', 'tasks.description')
    ->orderBy('tasks.id')
    ->get();

foreach ($rows as $row) {
    echo $row->id . "\t" . $row->title . PHP_EOL;
}
