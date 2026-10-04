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
    ->where('title', 'like', '[ATTEND-027]%')
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
    'content' => 'Update progress: ATTEND-027 Pengajuan lembur, approval, perhitungan, dan handoff payroll siap testing. Sudah dibuat tabel overtime_requests, model OvertimeRequest, controller OvertimeRequestController, halaman Pengajuan Lembur, route GET/POST attendance/overtime-requests, menu Operasional HR > Pengajuan Lembur, form request lembur, approval status, tipe lembur regular/night/rest day/holiday, kalkulasi requested hours dari start-end, multiplier awal, hourly rate, estimated amount, approved hours, handoff_to_payroll, payroll_component_code, calculation_snapshot, dan audit log overtime_request.created. Route smoke sukses dan full Laravel test 61 passed / 733 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-027',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
