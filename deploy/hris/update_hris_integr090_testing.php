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
    ->where('title', 'like', '[INTEGR-090]%')
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
    if (in_array('progress', $wbsColumns, true)) {
        $wbs->progress = 90;
    }
    if (in_array('status', $wbsColumns, true)) {
        $wbs->status = 'Testing';
    }
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: INTEGR-090 sudah masuk Testing. Implementasi mencakup halaman Export Pajak & BPJS, preview template pajak dan BPJS, ringkasan validasi data, validasi NIK/NPWP/PTKP/BPJS/verification status, endpoint download CSV pajak dan BPJS, menu Payroll & Tax, dan test Laravel untuk halaman + dua download template. Laravel test 44 passed / 514 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'INTEGR-090',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
    'wbs_progress' => $wbs?->progress ?? null,
    'wbs_status' => $wbs?->status ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
