<?php

use App\Models\Task;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$defaults = [
    'ASSESS-002' => ['2026-08-01', '2026-08-01'],
    'ASSESS-003' => ['2026-08-02', '2026-08-02'],
    'ASSESS-004' => ['2026-08-03', '2026-08-03'],
    'ASSESS-005' => ['2026-08-04', '2026-08-04'],
    'ASSESS-006' => ['2026-08-05', '2026-08-05'],
];

foreach ($defaults as $code => [$start, $due]) {
    $task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', 6))
        ->where('title', 'like', '['.$code.']%')
        ->first();

    if (! $task) {
        continue;
    }

    $task->start_date = Carbon::parse($start);
    $task->due_date = Carbon::parse($due);
    $task->duration = 1;
    $task->progress = $task->progress ?? 0;
    $task->save();

    echo $task->id.'|'.$task->taskList->name.'|'.$task->start_date.'|'.$task->due_date.'|'.$task->title.PHP_EOL;
}
