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
    ->where('title', 'like', '[PAYROL-033]%')
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
    'content' => 'Mulai dikerjakan: PAYROL-033 Manajemen BPJS Kesehatan/Ketenagakerjaan dan profil pajak. Scope awal: master BPJS employee, kelas BPJS, nomor Kesehatan/Ketenagakerjaan, effective date, contribution base, employer/employee rate, active flag, link ke employee/tax profile, halaman Payroll BPJS, audit log, dan test Laravel.',
    'mentions' => [],
    'likes_count' => 0,
]);

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo json_encode([
    'code' => 'PAYROL-033',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
], JSON_PRETTY_PRINT) . PHP_EOL;
