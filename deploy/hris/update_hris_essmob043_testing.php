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
    ->where('title', 'like', '[ESSMOB-043]%')
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
    'content' => 'Update progress: ESSMOB-043 Pengajuan update profil dengan approval dan bukti dokumen siap Testing. Sudah dibuat tabel ess_profile_update_requests, model EssProfileUpdateRequest, controller EssProfileUpdateController, halaman ESS Update Profil Saya, halaman HR Approval Update Profil, menu employee/admin, route ESS/admin, snapshot data lama vs request baru, bukti dokumen, approve/reject HR, apply perubahan ke EmployeeProfile dan rekening primary, audit log submit/review, guard jika user belum linked employee profile, focused test pass, smoke 8091/8092 pass, full Laravel test 85 passed / 1047 assertions.',
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
