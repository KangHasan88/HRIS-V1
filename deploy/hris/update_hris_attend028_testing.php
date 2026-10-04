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
    ->where('title', 'like', '[ATTEND-028]%')
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
    'content' => 'Update progress: ATTEND-028 Workflow koreksi absensi dan missing punch siap testing. Sudah dibuat tabel attendance_corrections, model AttendanceCorrection, controller AttendanceCorrectionController, halaman Koreksi Absensi, route GET/POST attendance/corrections, menu Operasional HR > Koreksi Absensi, form koreksi dengan link clock event lama, tipe missing clock-in/out, salah jam, salah tipe, duplicate punch, sumber request web admin/ESS/mobile WebView, status approval, generate event absensi otomatis saat approved, generated event number CORR-{nomor koreksi}, correction snapshot, attachment reference, reason, decision notes, dan audit log attendance_correction.created. Route smoke sukses dan full Laravel test passed.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-028',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
    'wbs_progress' => $wbs?->progress ?? null,
    'wbs_status' => $wbs?->status ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
