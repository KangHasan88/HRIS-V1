<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();
$fixed = [];

$tasks = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->whereNotNull('start_date')
    ->whereNotNull('due_date')
    ->get();

foreach ($tasks as $task) {
    $start = substr((string) $task->start_date, 0, 10);
    $due = substr((string) $task->due_date, 0, 10);
    if ($start > $due) {
        continue;
    }

    $expectedDuration = (new DateTime($start))->diff(new DateTime($due))->days + 1;
    $currentDuration = (int) ($task->duration ?? 0);

    if ($currentDuration === $expectedDuration) {
        continue;
    }

    $task->duration = $expectedDuration;
    $task->save();

    Comment::create([
        'task_id' => $task->id,
        'user_id' => $actor?->id,
        'content' => "Kanban/Gantt hygiene update: duration disinkronkan dari {$currentDuration} hari menjadi {$expectedDuration} hari sesuai start {$start} dan due {$due}.",
        'mentions' => [],
        'likes_count' => 0,
    ]);

    $fixed[] = [
        'task_id' => $task->id,
        'title' => $task->title,
        'from' => $currentDuration,
        'to' => $expectedDuration,
    ];
}

echo json_encode($fixed, JSON_PRETTY_PRINT) . PHP_EOL;
