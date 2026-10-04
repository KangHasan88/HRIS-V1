<?php

use App\Models\Task;
use App\Models\WbsItem;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$issues = [];

$tasks = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[TAX-%')
    ->orderBy('id')
    ->get();

foreach ($tasks as $task) {
    $wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
    $start = $task->start_date ? substr((string) $task->start_date, 0, 10) : null;
    $due = $task->due_date ? substr((string) $task->due_date, 0, 10) : null;
    $duration = (int) ($task->duration ?? 0);

    if (!$wbs) {
        $issues[] = "{$task->title}: WBS belum link ke card.";
    }
    if (!$start || !$due) {
        $issues[] = "{$task->title}: start/due date belum lengkap.";
    }
    if ($start && $due && $start > $due) {
        $issues[] = "{$task->title}: start date lebih besar dari due date.";
    }
    if ($start && $due) {
        $expectedDuration = (new DateTime($start))->diff(new DateTime($due))->days + 1;
        if ($duration !== $expectedDuration) {
            $issues[] = "{$task->title}: duration {$duration}, seharusnya {$expectedDuration} untuk {$start} - {$due}.";
        }
    }

    echo implode(' | ', [
        $task->id,
        $task->taskList?->name,
        ($task->progress ?? 0) . '%',
        $start ?: '-',
        $due ?: '-',
        $duration,
        $wbs ? 'WBS linked' : 'WBS missing',
        $task->title,
    ]) . PHP_EOL;
}

echo PHP_EOL;
if ($issues) {
    echo "ISSUES:" . PHP_EOL;
    foreach ($issues as $issue) {
        echo "- {$issue}" . PHP_EOL;
    }
    exit(1);
}

echo "OK: TAX WBS dan Gantt konsisten untuk {$tasks->count()} card." . PHP_EOL;
