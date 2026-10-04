<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$taskListIds = DB::table('task_lists')->where('board_id', $boardId)->pluck('id');
$totalTasks = DB::table('tasks')
    ->whereIn('task_list_id', $taskListIds)
    ->whereNull('archived_at')
    ->count();

$wbs = DB::table('wbs_items')
    ->leftJoin('tasks', 'tasks.id', '=', 'wbs_items.task_id')
    ->where('wbs_items.board_id', $boardId)
    ->select(
        'wbs_items.id',
        'wbs_items.code',
        'wbs_items.title',
        'wbs_items.task_id',
        'tasks.title as task_title',
        'tasks.start_date',
        'tasks.due_date',
        'tasks.progress',
        'tasks.task_list_id'
    )
    ->get();

$linkedTaskIds = $wbs->pluck('task_id')->filter()->unique()->values();
$unlinkedTasks = DB::table('tasks')
    ->whereIn('task_list_id', $taskListIds)
    ->whereNull('archived_at')
    ->whereNotIn('id', $linkedTaskIds)
    ->count();

$linkedMissingDates = $wbs->filter(function ($row) {
    return $row->task_id && (!$row->start_date || !$row->due_date);
});

$linkedOrphan = $wbs->filter(function ($row) use ($taskListIds) {
    return $row->task_id && (!$row->task_list_id || !$taskListIds->contains($row->task_list_id));
});

echo json_encode([
    'board_id' => $boardId,
    'total_active_cards' => $totalTasks,
    'wbs_items' => $wbs->count(),
    'linked_wbs_items' => $linkedTaskIds->count(),
    'cards_not_in_wbs' => $unlinkedTasks,
    'linked_wbs_missing_gantt_dates' => $linkedMissingDates->count(),
    'linked_wbs_orphan_cards' => $linkedOrphan->count(),
    'sample_linked_rows' => $wbs->whereNotNull('task_id')->take(5)->map(fn ($row) => [
        'wbs_code' => $row->code,
        'wbs_title' => $row->title,
        'card_title' => $row->task_title,
        'start_date' => $row->start_date,
        'due_date' => $row->due_date,
        'progress' => $row->progress,
    ])->values(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
