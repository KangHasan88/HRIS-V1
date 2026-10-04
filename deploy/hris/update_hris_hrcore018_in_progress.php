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
    ->where('title', 'like', '[HRCORE-018]%')
    ->firstOrFail();

$inProgress = TaskList::where('board_id', $boardId)->where('name', 'In Progress')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $inProgress->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 15;
}
$task->save();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Mulai dikerjakan: HRCORE-018 Riwayat disiplin, surat peringatan, dan coaching. Scope awal: tabel employee_disciplinary_records, jenis case coaching/verbal warning/SP1/SP2/SP3/investigasi, tanggal kejadian, issued date, valid sampai, corrective action, owner/approver, status, follow-up date, attachment reference, risk level, halaman Disiplin & Coaching, audit log, dan test Laravel.',
    'mentions' => [],
    'likes_count' => 0,
]);

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo json_encode([
    'code' => 'HRCORE-018',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
], JSON_PRETTY_PRINT) . PHP_EOL;
