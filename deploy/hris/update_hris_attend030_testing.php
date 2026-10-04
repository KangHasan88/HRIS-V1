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
    ->where('title', 'like', '[ATTEND-030]%')
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
    'content' => 'Update progress: ATTEND-030 Alert fatigue, keterlambatan, absensi, dan kepatuhan siap testing. Sudah dibuat tabel attendance_alerts, model AttendanceAlert, controller AttendanceAlertController, halaman Alert Absensi, route GET/POST attendance/alerts, menu Operasional HR > Alert Absensi, summary total/perlu follow-up/high-critical/resolved, form alert dengan employee, clock event, roster shift, tipe late/absence/fatigue/early leave/missing punch/compliance, severity, status follow-up, menit terlambat, jam kerja aktual, fatigue score, rule compliance, owner, due follow-up, trigger reason, follow-up notes, snapshot alert, resolved tracking, dan audit log attendance_alert.created. Route smoke sukses dan full Laravel test passed.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-030',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
    'wbs_progress' => $wbs?->progress ?? null,
    'wbs_status' => $wbs?->status ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
