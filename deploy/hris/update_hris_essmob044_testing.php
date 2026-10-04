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
    ->where('title', 'like', '[ESSMOB-044]%')
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
    'content' => 'Update progress: ESSMOB-044 Pengajuan cuti, lembur, dinas, dan koreksi absensi siap testing. Sudah dibuat tabel business_trip_requests, model BusinessTripRequest, controller EssRequestCenterController, halaman Request Center ESS, route GET ess/request-center, POST overtime/trips/corrections, menu Employee Self Service > Request Center, form cuti terhubung ke engine ESS cuti existing, form lembur ESS dengan kalkulasi requested hours dan multiplier awal, form dinas dengan tujuan/periode/budget/snapshot durasi, form koreksi absensi ESS tanpa generate event sampai approval, semua request scoped ke employee login, status submitted, approver Atasan langsung, dan audit log ess_overtime_request.submitted + ess_business_trip.submitted + ess_attendance_correction.submitted. Route smoke sukses dan full Laravel test passed.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ESSMOB-044',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
    'wbs_progress' => $wbs?->progress ?? null,
    'wbs_status' => $wbs?->status ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
