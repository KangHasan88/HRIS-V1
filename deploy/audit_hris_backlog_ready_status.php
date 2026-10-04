<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$cards = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->whereIn('task_lists.name', ['Backlog', 'Ready'])
    ->select(
        'tasks.id',
        'tasks.title',
        'tasks.priority',
        'tasks.progress',
        'tasks.start_date',
        'tasks.due_date',
        'tasks.order',
        'task_lists.name as list'
    )
    ->orderBy('task_lists.name')
    ->orderBy('tasks.start_date')
    ->orderBy('tasks.order')
    ->get();

$summary = $cards->groupBy('list')->map(function ($items) {
    return [
        'count' => $items->count(),
        'min_start' => $items->min('start_date'),
        'max_due' => $items->max('due_date'),
        'by_prefix' => $items->groupBy(function ($task) {
            if (preg_match('/^\[([A-Z]+)-/', $task->title, $m)) {
                return $m[1];
            }
            return 'OTHER';
        })->map->count()->sortKeys(),
    ];
});

$shouldBeReady = $cards->filter(function ($task) {
    if ($task->list !== 'Backlog') {
        return false;
    }
    if (!$task->start_date) {
        return false;
    }
    return $task->start_date <= '2026-07-13'
        || str_starts_with($task->title, '[ROADMAP-')
        || str_starts_with($task->title, '[ARCHIT-')
        || str_starts_with($task->title, '[SECURE-')
        || str_starts_with($task->title, '[ADMIN-');
})->values();

$readyTooLate = $cards->filter(function ($task) {
    return $task->list === 'Ready' && $task->start_date && $task->start_date > '2026-07-20';
})->values();

$readyDaily = $cards->filter(function ($task) {
    return $task->list === 'Ready' && str_starts_with($task->title, '[DAY-');
})->values();

$firstBacklog = $cards
    ->where('list', 'Backlog')
    ->sortBy([['start_date', 'asc'], ['order', 'asc']])
    ->take(35)
    ->values();

$readyCards = $cards
    ->where('list', 'Ready')
    ->sortBy([['start_date', 'asc'], ['order', 'asc']])
    ->values();

echo json_encode([
    'board_id' => $boardId,
    'summary' => $summary,
    'should_be_ready_candidates' => $shouldBeReady->map(fn ($t) => [
        'id' => $t->id,
        'title' => $t->title,
        'start_date' => $t->start_date,
        'due_date' => $t->due_date,
        'priority' => $t->priority,
    ]),
    'ready_too_late_candidates' => $readyTooLate->map(fn ($t) => [
        'id' => $t->id,
        'title' => $t->title,
        'start_date' => $t->start_date,
        'due_date' => $t->due_date,
    ]),
    'ready_daily_cards' => $readyDaily->map(fn ($t) => [
        'id' => $t->id,
        'title' => $t->title,
        'start_date' => $t->start_date,
        'due_date' => $t->due_date,
    ]),
    'ready_cards' => $readyCards->map(fn ($t) => [
        'id' => $t->id,
        'title' => $t->title,
        'start_date' => $t->start_date,
        'due_date' => $t->due_date,
        'priority' => $t->priority,
    ]),
    'first_backlog_cards' => $firstBacklog->map(fn ($t) => [
        'id' => $t->id,
        'title' => $t->title,
        'start_date' => $t->start_date,
        'due_date' => $t->due_date,
        'priority' => $t->priority,
    ]),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
