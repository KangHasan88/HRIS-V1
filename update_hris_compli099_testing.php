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
    ->where('title', 'like', '[COMPLI-099]%')
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
    'content' => 'Testing 90%: COMPLI-099 Monitoring SLA, dashboard job queue, dan health integrasi selesai. Implementasi mencakup IntegrationHealthController, route /settings/integration-health, menu Health Integrasi & SLA di System Administration, health score, queue backlog, failed jobs, oldest job age, failed job per queue, SLA approval cuti/lembur, notifikasi queued/stale/failed, connector health untuk biometric attendance agent, Coretax, Email & Calendar, WhatsApp Gateway, status connector, action item operasional, feature test, smoke HTTPS, dan full regression test. URL: https://31.97.106.123:8091/settings/integration-health. Full test: 134 passed. Smoke HTTPS: status 200 page ok.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'COMPLI-099',
    'task_id' => $task->id,
    'title' => Str::limit($task->title, 100),
    'list' => $testingList->name,
    'progress' => $task->progress ?? null,
    'checked' => ChecklistItem::whereIn('checklist_id', $checklistIds)->where('is_checked', true)->count(),
    'total_checklist' => ChecklistItem::whereIn('checklist_id', $checklistIds)->count(),
    'wbs' => $wbs?->code,
], JSON_PRETTY_PRINT).PHP_EOL;
