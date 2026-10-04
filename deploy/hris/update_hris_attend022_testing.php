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
    ->where('title', 'like', '[ATTEND-022]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: ATTEND-022 Clock-in/out mobile siap testing. Sudah dibuat tabel attendance_device_bindings dan attendance_clock_events, model AttendanceDeviceBinding/AttendanceClockEvent, controller AttendanceClockController, halaman Clock Mobile dengan form section pattern terbaru, route GET/POST attendance/clock-events, menu Operasional HR, GPS coordinate + accuracy, selfie reference, liveness score/pass flag, device UID/name/platform/app version, device binding trusted/pending, risk flags gps_missing/gps_low_accuracy/selfie_missing/liveness_low_score/device_not_trusted, auto downgrade approved ke Need Review jika ada risk, dan audit log attendance_clock_event.created. Migration sukses, Blade cache sukses, Laravel test 55 passed / 650 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-022',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
