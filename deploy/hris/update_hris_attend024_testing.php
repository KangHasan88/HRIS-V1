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
    ->where('title', 'like', '[ATTEND-024]%')
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
    'content' => 'Update progress: ATTEND-024 Import absensi biometric/mesin siap testing. Sudah dibuat tabel attendance_import_batches dan attendance_import_rows, model AttendanceImportBatch/AttendanceImportRow, controller AttendanceImportController, halaman Import Absensi dengan form compact + panduan form, route GET/POST attendance/imports, menu Operasional HR > Import Absensi, batch import source biometric/CSV/API/manual, row import clock-in/out/break, auto match employee dari nomor karyawan/raw identifier, rekonsiliasi anomali employee_not_found/duplicate_event/missing_clock_in, counter imported/matched/anomaly, audit log batch + row. Migration sukses, Blade cache sukses, Laravel test 57 passed / 683 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-024',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
