<?php

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where('title', 'like', '[MANAGE-056]%')
    ->firstOrFail();

$task->task_list_id = TaskList::where('board_id', 6)->where('name', 'Testing')->firstOrFail()->id;
$task->progress = 90;
$task->save();

$checklistIds = Checklist::where('task_id', $task->id)->pluck('id');
ChecklistItem::whereIn('checklist_id', $checklistIds)->update(['is_checked' => true]);

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();
$wbs = WbsItem::where('board_id', 6)->where('task_id', $task->id)->first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Testing 90%: MANAGE-056 selesai. Dokumen tim, approval surat kerja, scope manager, audit trail, menu, test 93 passed, smoke HTTPS 200.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'MANAGE-056',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress,
    'checked' => ChecklistItem::whereIn('checklist_id', $checklistIds)->where('is_checked', true)->count(),
    'total_checklist' => ChecklistItem::whereIn('checklist_id', $checklistIds)->count(),
    'wbs' => $wbs?->code,
    'wbs_title' => $wbs?->title,
], JSON_PRETTY_PRINT) . PHP_EOL;
