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
    ->where('title', 'like', '[ESSMOB-047]%')
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
    $columns = Schema::getColumnListing('wbs_items');
    if (in_array('status', $columns, true)) {
        $wbs->status = 'Testing';
    }
    if (in_array('progress', $columns, true)) {
        $wbs->progress = 90;
    }
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: ESSMOB-047 Helpdesk HR siap Testing. Implementasi sudah mencakup tabel hr_helpdesk_tickets dan hr_helpdesk_messages, model ticket/message, controller submit tiket ESS, reply karyawan, admin HR queue, update status/PIC/SLA, balasan HR, catatan internal, attachment reference, audit log, menu ESS/admin, shortcut Beranda ESS, dan demo employee link untuk Bima/Dimas/Nadia agar ESS bisa dipakai. Full Laravel test 87 passed / 1088 assertions. Smoke HTTPS pass: ESS 8092 GET/POST /ess/helpdesk, admin 8091 /employees/helpdesk, tiket smoke muncul di ESS dan admin queue.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'task_id' => $task->id,
    'title' => $task->title,
    'list' => $testing->name,
    'progress' => $task->progress ?? null,
    'wbs_id' => $wbs?->id,
    'wbs_status' => $wbs?->status ?? null,
    'wbs_progress' => $wbs?->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
