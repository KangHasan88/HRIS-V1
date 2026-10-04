<?php

use App\Models\Board;
use App\Models\Task;
use App\Models\TaskActivity;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$board = Board::query()->findOrFail(6);
$inProgress = $board->lists()->where('name', 'In Progress')->firstOrFail();
$task = Task::query()->findOrFail(387);
$task->task_list_id = $inProgress->id;
$task->progress = max((int) $task->progress, 10);
$task->save();

TaskActivity::query()->create([
    'task_id' => $task->id,
    'user_id' => $board->user_id,
    'action' => 'commented',
    'old_value' => null,
    'new_value' => 'Mulai eksekusi INTEGR-093: Local Attendance Agent MVP. Scope: endpoint HRIS untuk push clock event dari agent lokal, log sync connector, batch Import Absensi, dan skeleton agent Windows CSV watcher.',
]);

echo "INTEGR-093 moved to In Progress\n";
