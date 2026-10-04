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
    ->where('title', 'like', '[ATTEND-023]%')
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
    'content' => 'Update progress: ATTEND-023 Geofence Attendance siap testing. Sudah dibuat tabel attendance_geofences dan attendance_location_policies, model AttendanceGeofence/AttendanceLocationPolicy, controller AttendanceGeofenceController, halaman Geofence Attendance dengan pattern form section, route GET/POST attendance/geofences, menu Operasional HR, master lokasi kantor/site/WFH/client site/perjalanan dinas/temporary, radius GPS, aturan clock-in/out, wajib selfie/liveness, policy lokasi per employee/company/branch, periode approval, allowed days, simulasi validasi jarak GPS terhadap radius, dan audit log geofence + policy. Migration sempat kena limit nama index MySQL lalu diperbaiki dengan index pendek. Migration final sukses, Blade cache sukses, Laravel test 56 passed / 665 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-023',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
