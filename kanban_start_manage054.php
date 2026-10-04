<?php

use App\Models\Board;
use App\Models\Task;
use App\Models\TaskActivity;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$board = Board::query()->findOrFail(6);
$inProgress = $board->lists()->where('name', 'In Progress')->firstOrFail();
$task = Task::query()->findOrFail(255);
$task->task_list_id = $inProgress->id;
$task->progress = max((int) $task->progress, 10);
$task->save();
TaskActivity::query()->create([
    'task_id' => $task->id,
    'user_id' => $board->user_id,
    'action' => 'commented',
    'old_value' => null,
    'new_value' => 'Mulai eksekusi MANAGE-054: catatan performa bawahan, log one-on-one, action item, follow-up, visibility manager, audit trail, test dan smoke.',
]);
echo "MANAGE-054 moved to In Progress\n";
