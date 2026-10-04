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
    ->where('title', 'like', '[ESSMOB-046]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
if ($wbs) {
    $wbsColumns = Schema::getColumnListing('wbs_items');
    if (in_array('status', $wbsColumns, true)) {
        $wbs->status = 'Testing';
    }
    if (in_array('progress', $wbsColumns, true)) {
        $wbs->progress = 90;
    }
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: ESSMOB-046 Pengajuan reimbursement dengan foto struk dari kamera siap Testing. Implementasi memakai manager_claim_requests existing agar klaim ESS masuk ke budget/manager visibility. Ditambahkan field request_source, merchant_name, receipt_capture_method, receipt_captured_at, receipt_metadata, controller EssReimbursementController, halaman ESS Reimbursement Saya, route GET/POST ess/reimbursements, menu employee, shortcut ESS home, submit klaim scoped ke employee login, status Submitted, approver Atasan langsung, metadata bukti struk kamera vs non-kamera, audit log ess_reimbursement.submitted, focused test pass, smoke 8092 reimbursement dan 8091 manager budget pass, full Laravel test 86 passed / 1062 assertions.',
]);

echo json_encode([
    'task_id' => $task->id,
    'task_title' => $task->title,
    'list' => $testing->name,
    'progress' => $task->progress ?? null,
    'wbs_id' => $wbs?->id,
    'wbs_status' => $wbs?->status ?? null,
    'wbs_progress' => $wbs?->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
