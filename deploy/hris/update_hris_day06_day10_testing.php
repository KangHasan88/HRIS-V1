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
$codes = ['DAY-06', 'DAY-07', 'DAY-08', 'DAY-09', 'DAY-10'];
$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();
$updated = [];

foreach ($codes as $code) {
    $task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
        ->where('title', 'like', '[' . $code . ']%')
        ->first();

    if (!$task) {
        continue;
    }

    $task->task_list_id = $testing->id;
    if (Schema::hasColumn('tasks', 'progress')) {
        $task->progress = 90;
    }
    $task->save();

    $wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
    if ($wbs) {
        $columns = Schema::getColumnListing('wbs_items');
        if (in_array('progress', $columns, true)) {
            $wbs->progress = 90;
        }
        if (in_array('status', $columns, true)) {
            $wbs->status = 'Testing';
        }
        $wbs->save();
    }

    Comment::create([
        'task_id' => $task->id,
        'user_id' => $actor?->id,
        'content' => 'Milestone harian diselaraskan ke Testing 90%. Scope terkait sudah tercakup oleh card implementasi detail yang telah diuji: employee master, onboarding/offboarding, core attendance, cuti, lembur, ESS, manager review, analytics, dan integrasi pendukung. Status Card/WBS/Gantt dirapikan agar tidak muncul overdue palsu.',
        'mentions' => [],
        'likes_count' => 0,
    ]);

    $updated[] = [
        'code' => $code,
        'task_id' => $task->id,
        'list' => $task->taskList()->value('name'),
        'progress' => $task->progress ?? null,
        'wbs' => $wbs?->code,
        'wbs_progress' => $wbs?->progress ?? null,
        'wbs_status' => $wbs?->status ?? null,
    ];
}

echo json_encode($updated, JSON_PRETTY_PRINT) . PHP_EOL;
