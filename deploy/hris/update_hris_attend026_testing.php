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
    ->where('title', 'like', '[ATTEND-026]%')
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
    'content' => 'Update progress: ATTEND-026 Pengajuan dan approval cuti dengan simulasi saldo siap testing. Sudah dibuat tabel leave_requests, model LeaveRequest, controller LeaveRequestController, halaman Pengajuan Cuti, route GET/POST leave/requests, menu Operasional HR > Pengajuan Cuti, form cek simulasi saldo, submit request dari Web Admin/ESS WebView/Mobile WebView, status draft/submitted/approved/rejected/cancelled, approver saat ini, referensi lampiran, alasan dan catatan keputusan, kalkulasi hari cuti berdasarkan policy aktif, exclude weekend/libur sesuai rule, saldo sebelum/sesudah, warning over balance, simulation_snapshot, dan audit log leave_request.created. Route smoke sukses dan Laravel test 59 passed / 709 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-026',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
