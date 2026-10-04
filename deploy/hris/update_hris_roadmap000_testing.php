<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[ROADMAP-000]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
if ($wbs && Schema::hasColumn('wbs_items', 'progress')) {
    $wbs->progress = 90;
}
if ($wbs && Schema::hasColumn('wbs_items', 'status')) {
    $wbs->status = 'Testing';
}
if ($wbs) {
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: ROADMAP-000 Strategi produk HRIS enterprise dan slicing rilis siap testing. Sudah dibuat halaman Product Roadmap di Project Delivery, route GET settings/product-roadmap, controller ProductRoadmapController, menu Project Delivery > Product Roadmap, ringkasan readiness gate, slicing rilis 1 bulan, roadmap per modul, next slice, risiko overdue aktif, dan integrasi live dengan data Kanban/WBS. Route smoke sukses dan full Laravel test passed.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ROADMAP-000',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
    'wbs_progress' => $wbs?->progress ?? null,
    'wbs_status' => $wbs?->status ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
