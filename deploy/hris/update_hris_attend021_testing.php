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
    ->where('title', 'like', '[ATTEND-021]%')
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
    'content' => 'Update progress: ATTEND-021 Jadwal Shift siap testing. Sudah dibuat tabel attendance_shift_patterns dan attendance_shift_assignments, model AttendanceShiftPattern/AttendanceShiftAssignment, controller AttendanceShiftScheduleController, halaman Jadwal Shift dengan form section pattern terbaru, route GET/POST attendance/shift-schedules, menu Operasional HR, permission manage shift schedules, master shift reguler/rotasi/split/long/night, cross-day, split shift, grace late/early leave, roster scope employee/company/branch, rotasi fixed/weekly/2-week/monthly/custom, work day/off day, status draft/published/archived, dan audit log pattern + assignment. Migration sukses, seeder permission sukses, Blade cache sukses, Laravel test 54 passed / 638 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-021',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
