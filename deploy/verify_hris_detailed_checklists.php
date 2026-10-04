<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$summary = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->join('checklists', 'checklists.task_id', '=', 'tasks.id')
    ->join('checklist_items', 'checklist_items.checklist_id', '=', 'checklists.id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->selectRaw('COUNT(DISTINCT tasks.id) as cards_with_checklists, COUNT(DISTINCT checklists.id) as checklists, COUNT(checklist_items.id) as items')
    ->first();

$sample = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->where('tasks.title', 'not like', '[ROADMAP-000]%')
    ->select('tasks.id', 'tasks.title')
    ->orderBy('tasks.order')
    ->first();

$sampleItems = [];
if ($sample) {
    $sampleItems = DB::table('checklist_items')
        ->join('checklists', 'checklists.id', '=', 'checklist_items.checklist_id')
        ->where('checklists.task_id', $sample->id)
        ->orderBy('checklists.order')
        ->orderBy('checklist_items.order')
        ->pluck('checklist_items.name')
        ->take(12)
        ->all();
}

echo json_encode([
    'board_id' => $boardId,
    'summary' => $summary,
    'sample_card' => $sample,
    'sample_items' => $sampleItems,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
