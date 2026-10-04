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
    ->where('title', 'like', '[AUTH-002]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: role awal HRIS sudah dirapikan menjadi Super Admin, HR Admin, Payroll Admin, Manager, dan Employee. Permission matrix sudah ditambah untuk module switcher, delivery progress, payroll settings, BPJS/PPh 21/Coretax readiness. User seeder demo sudah disesuaikan dan test Laravel 8 passed.',
    'mentions' => [],
    'likes_count' => 0,
]);

$summary = [
    'code' => 'AUTH-002',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
    'cards_not_in_wbs' => Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
        ->whereDoesntHave('wbsItems')
        ->count(),
];

echo json_encode($summary, JSON_PRETTY_PRINT) . PHP_EOL;
