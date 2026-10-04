<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$now = now();
$boardId = 6;
$readyUntil = '2026-07-13';

$lists = DB::table('task_lists')
    ->where('board_id', $boardId)
    ->whereIn('name', ['Backlog', 'Ready'])
    ->pluck('id', 'name');

if (!$lists->has('Backlog') || !$lists->has('Ready')) {
    throw new RuntimeException('Backlog/Ready list not found for HRIS board.');
}

$backlogId = $lists['Backlog'];
$readyId = $lists['Ready'];

$moveToReady = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->where('tasks.task_list_id', $backlogId)
    ->whereNull('tasks.archived_at')
    ->where(function ($query) use ($readyUntil) {
        $query->where('tasks.title', 'like', '[ROADMAP-%')
            ->orWhere(function ($sub) use ($readyUntil) {
                $sub->whereNot('tasks.title', 'like', '[DAY-%')
                    ->whereNotNull('tasks.start_date')
                    ->where('tasks.start_date', '<=', $readyUntil);
            });
    })
    ->select('tasks.id', 'tasks.title', 'tasks.start_date', 'tasks.due_date')
    ->get();

$moveFutureDailyToBacklog = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->where('tasks.task_list_id', $readyId)
    ->whereNull('tasks.archived_at')
    ->where('tasks.title', 'like', '[DAY-%')
    ->where('tasks.start_date', '>', $readyUntil)
    ->select('tasks.id', 'tasks.title', 'tasks.start_date', 'tasks.due_date')
    ->get();

$readyOrder = (int) DB::table('tasks')->where('task_list_id', $readyId)->max('order');
foreach ($moveToReady as $task) {
    $readyOrder++;
    DB::table('tasks')->where('id', $task->id)->update([
        'task_list_id' => $readyId,
        'order' => $readyOrder,
        'updated_at' => $now,
    ]);
}

$backlogOrder = (int) DB::table('tasks')->where('task_list_id', $backlogId)->max('order');
foreach ($moveFutureDailyToBacklog as $task) {
    $backlogOrder++;
    DB::table('tasks')->where('id', $task->id)->update([
        'task_list_id' => $backlogId,
        'order' => $backlogOrder,
        'updated_at' => $now,
    ]);
}

$cards = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->whereIn('task_lists.name', ['Backlog', 'Ready'])
    ->select('tasks.title', 'tasks.start_date', 'tasks.due_date', 'task_lists.name as list')
    ->orderBy('task_lists.name')
    ->orderBy('tasks.start_date')
    ->orderBy('tasks.order')
    ->get();

$summary = $cards->groupBy('list')->map(function ($items) {
    return [
        'count' => $items->count(),
        'min_start' => $items->min('start_date'),
        'max_due' => $items->max('due_date'),
        'prefixes' => $items->groupBy(function ($task) {
            if (preg_match('/^\[([A-Z]+)-/', $task->title, $m)) {
                return $m[1];
            }
            return 'OTHER';
        })->map->count()->sortKeys(),
    ];
});

$readyCards = $cards->where('list', 'Ready')->values();
$readyFuture = $readyCards->filter(function ($task) use ($readyUntil) {
    return $task->start_date > $readyUntil && !str_starts_with($task->title, '[ROADMAP-');
})->values();
$backlogEarly = $cards->where('list', 'Backlog')->filter(function ($task) use ($readyUntil) {
    return $task->start_date <= $readyUntil && !str_starts_with($task->title, '[DAY-');
})->values();

echo json_encode([
    'board_id' => $boardId,
    'ready_until' => $readyUntil,
    'moved_to_ready' => $moveToReady->map(fn ($t) => $t->title)->values(),
    'moved_future_daily_to_backlog' => $moveFutureDailyToBacklog->map(fn ($t) => $t->title)->values(),
    'summary_after' => $summary,
    'ready_future_non_roadmap_after' => $readyFuture->map(fn ($t) => [
        'title' => $t->title,
        'start_date' => $t->start_date,
    ])->values(),
    'backlog_early_non_day_after' => $backlogEarly->map(fn ($t) => [
        'title' => $t->title,
        'start_date' => $t->start_date,
    ])->values(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
