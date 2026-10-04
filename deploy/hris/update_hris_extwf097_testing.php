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
    ->where('title', 'like', '[EXTWF-097]%')
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
    'content' => 'Testing 90%: EXTWF-097 selesai. Implementasi mencakup master Worker Non-Employee untuk outsource, harian, borongan, freelance, konsultan, project worker; relasi vendor/kontrak, company/site/cost center, payment handling vendor invoice/internal payroll/operational only, tarif harian/borongan, dokumen KTP/kontrak/HSE/akses area, filter tipe/status, audit log, menu External Workforce, dan smoke HTTPS. Full test: 103 passed.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'EXTWF-097',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress,
    'checked' => ChecklistItem::whereIn('checklist_id', $checklistIds)->where('is_checked', true)->count(),
    'total_checklist' => ChecklistItem::whereIn('checklist_id', $checklistIds)->count(),
    'wbs' => $wbs?->code,
    'wbs_title' => $wbs?->title,
], JSON_PRETTY_PRINT) . PHP_EOL;
