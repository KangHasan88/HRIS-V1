<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$daily = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->where('tasks.title', 'like', '[DAY-%')
    ->whereNull('tasks.archived_at')
    ->select('tasks.title', 'tasks.start_date', 'tasks.due_date', 'task_lists.name as list')
    ->orderBy('tasks.start_date')
    ->get();

$featureByDate = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->where('tasks.title', 'not like', '[DAY-%')
    ->where('tasks.title', 'not like', '[ROADMAP-000]%')
    ->whereNull('tasks.archived_at')
    ->selectRaw('tasks.due_date, COUNT(*) as cards')
    ->groupBy('tasks.due_date')
    ->orderBy('tasks.due_date')
    ->get();

$dateCoverage = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->selectRaw('MIN(tasks.start_date) as min_start, MAX(tasks.due_date) as max_due, COUNT(*) as total_cards, SUM(CASE WHEN tasks.start_date IS NULL OR tasks.due_date IS NULL THEN 1 ELSE 0 END) as missing_dates')
    ->first();

echo json_encode([
    'coverage' => $dateCoverage,
    'daily_cards' => $daily->count(),
    'first_5_daily_cards' => $daily->take(5),
    'last_5_daily_cards' => $daily->slice(max(0, $daily->count() - 5))->values(),
    'feature_distribution_by_due_date' => $featureByDate,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
