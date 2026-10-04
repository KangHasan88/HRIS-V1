<?php

use App\Models\Task;
use App\Models\Comment;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();
$userId = $actor?->id;
$inProgress = TaskList::where('board_id', $boardId)->where('name', 'In Progress')->firstOrFail();
$ready = TaskList::where('board_id', $boardId)->where('name', 'Ready')->firstOrFail();

$updates = [
    'AUTH-001' => [
        'list_id' => $inProgress->id,
        'progress' => 65,
        'comment' => 'Update progress: login, logout, session, remember me UI, rate limit, dan test dasar sudah dibuat. Reset password masih menjadi pekerjaan lanjutan.',
    ],
    'AUTH-002' => [
        'list_id' => $inProgress->id,
        'progress' => 70,
        'comment' => 'Update progress: role awal dan user seeder sudah aktif di MySQL. Permission matrix perlu diperdalam saat modul mulai dibuat.',
    ],
    'ADMIN-001' => [
        'list_id' => $inProgress->id,
        'progress' => 75,
        'comment' => 'Update progress: admin panel shell, sidebar, topbar, dashboard awal, dan pattern SBB dengan accent #10B981 sudah diterapkan.',
    ],
    'ADMIN-002' => [
        'list_id' => $ready->id,
        'progress' => 40,
        'comment' => 'Update progress: konsep module switcher sudah tampil di dashboard, tetapi CRUD enable/disable per company belum dibuat.',
    ],
    'ADMIN-003' => [
        'list_id' => $ready->id,
        'progress' => 50,
        'comment' => 'Update progress: summary progress delivery awal sudah tampil. Integrasi live Kanban/WBS/Gantt masih perlu endpoint resmi.',
    ],
];

$taskColumns = Schema::getColumnListing('tasks');
$hasProgress = in_array('progress', $taskColumns, true);
$hasStartDate = in_array('start_date', $taskColumns, true);
$hasEndDate = in_array('end_date', $taskColumns, true);

$updated = [];
foreach ($updates as $code => $data) {
    $task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
        ->where('title', 'like', '[' . $code . ']%')
        ->firstOrFail();

    $task->task_list_id = $data['list_id'];
    if ($hasProgress) {
        $task->progress = $data['progress'];
    }
    $task->save();

    $wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
    if ($wbs) {
        if ($hasStartDate && empty($task->start_date)) {
            $task->start_date = $task->due_date;
        }
        if ($hasEndDate && empty($task->end_date)) {
            $task->end_date = $task->due_date;
        }
    }

    Comment::create([
        'task_id' => $task->id,
        'user_id' => $userId,
        'content' => $data['comment'],
        'mentions' => [],
        'likes_count' => 0,
    ]);

    $updated[] = [
        'code' => $code,
        'task_id' => $task->id,
        'list' => $task->taskList()->value('name'),
        'progress' => $hasProgress ? $task->progress : null,
        'wbs' => $wbs ? $wbs->code : null,
        'due_date' => optional($task->due_date)->format('Y-m-d'),
    ];
}

$summary = [
    'total_cards' => Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))->count(),
    'wbs_items' => WbsItem::where('board_id', $boardId)->count(),
    'linked_wbs' => WbsItem::where('board_id', $boardId)->whereNotNull('task_id')->count(),
    'cards_not_in_wbs' => Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
        ->whereDoesntHave('wbsItems')
        ->count(),
];

echo json_encode(['updated' => $updated, 'summary' => $summary], JSON_PRETTY_PRINT) . PHP_EOL;
