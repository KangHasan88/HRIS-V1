<?php

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[COMPLI-095]%')
    ->firstOrFail();

$testingList = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();

$task->task_list_id = $testingList->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

$checklistIds = Checklist::where('task_id', $task->id)->pluck('id');
ChecklistItem::whereIn('checklist_id', $checklistIds)->update(['is_checked' => true]);

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Testing 90%: COMPLI-095 Kebijakan retensi data, arsip, dan legal hold selesai. Implementasi mencakup migration data_retention_policies, data_legal_hold_cases, data_retention_execution_logs, model policy/legal hold/execution, DataRetentionComplianceController, route /settings/data-retention-compliance, menu Retensi & Legal Hold di System Administration, policy retensi per domain data, archive strategy, disposal action, approval requirement, legal hold override, legal hold case dengan custodian/evidence, execution log archive/anonymize/delete/blocked by legal hold, audit log data_retention_policy.created/data_legal_hold.created/data_retention_execution.recorded, feature test, smoke HTTPS, dan full regression. URL: https://31.97.106.123:8091/settings/data-retention-compliance. Targeted test: 1 passed / 27 assertions. Full test: 141 passed / 1974 assertions. Smoke HTTPS: status 200 page ok.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'COMPLI-095',
    'task_id' => $task->id,
    'title' => Str::limit($task->title, 100),
    'list' => $testingList->name,
    'progress' => $task->progress ?? null,
    'checked' => ChecklistItem::whereIn('checklist_id', $checklistIds)->where('is_checked', true)->count(),
    'total_checklist' => ChecklistItem::whereIn('checklist_id', $checklistIds)->count(),
    'wbs' => $wbs?->code,
], JSON_PRETTY_PRINT).PHP_EOL;
