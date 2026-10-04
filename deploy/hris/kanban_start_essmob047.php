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

$inProgress = TaskList::where('board_id', $boardId)->where('name', 'In Progress')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $inProgress->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 25;
}
$task->save();

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
if ($wbs) {
    $columns = Schema::getColumnListing('wbs_items');
    if (in_array('status', $columns, true)) {
        $wbs->status = 'In Progress';
    }
    if (in_array('progress', $columns, true)) {
        $wbs->progress = 25;
    }
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Mulai eksekusi ESSMOB-047: Tiket helpdesk HR dengan SLA, kategori, lampiran, dan chat. Scope awal: model/database ticket, ESS halaman pengajuan dan riwayat tiket karyawan, admin HR queue untuk update status/SLA, lampiran reference, komentar/chat internal, menu ESS, test dan smoke HTTPS 8092/8091.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'task_id' => $task->id,
    'title' => $task->title,
    'list' => $inProgress->name,
    'progress' => $task->progress ?? null,
    'wbs_id' => $wbs?->id,
    'wbs_status' => $wbs?->status ?? null,
    'wbs_progress' => $wbs?->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
