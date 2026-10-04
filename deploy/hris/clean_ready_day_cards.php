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
$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$updates = [
    'DAY-01' => 'Scope login dan admin panel awal sudah tersedia: login, reset password, admin shell, dashboard awal, dan test auth berjalan.',
    'DAY-02' => 'Scope admin shell dan module switcher sudah tersedia: layout sidebar/topbar, module switcher per company, role access, dan test module setting berjalan.',
    'DAY-03' => 'Scope progress dashboard serta WBS/Gantt summary sudah tersedia di delivery progress/Kanban integration, dan progress tracking dipakai dalam update card.',
    'DAY-04' => 'Scope RBAC, audit, dan tenancy sudah tersedia: role & permission, user assignment, organization scope, audit trail, access review, dan related tests berjalan.',
];

$result = [];

foreach ($updates as $code => $note) {
    $task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
        ->where('title', 'like', '[' . $code . ']%')
        ->first();

    if (!$task) {
        $result[] = ['code' => $code, 'status' => 'not_found'];
        continue;
    }

    $task->task_list_id = $testing->id;
    if (Schema::hasColumn('tasks', 'progress')) {
        $task->progress = 90;
    }
    $task->save();

    Comment::create([
        'task_id' => $task->id,
        'user_id' => $actor?->id,
        'content' => 'Kanban hygiene update: card dipindahkan dari Ready ke Testing karena scope target harian sudah tercakup oleh implementasi yang sudah berjalan. ' . $note . ' Selanjutnya card harian tidak boleh tertahan overdue di Ready; Ready hanya untuk pekerjaan yang benar-benar siap dikerjakan.',
        'mentions' => [],
        'likes_count' => 0,
    ]);

    $result[] = [
        'code' => $code,
        'task_id' => $task->id,
        'list' => $task->taskList()->value('name'),
        'progress' => $task->progress ?? null,
        'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
