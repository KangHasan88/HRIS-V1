<?php

use App\Models\Task;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$today = now()->toDateString();
$issues = [];
$summary = [];

$wbsColumns = Schema::getColumnListing('wbs_items');
$taskColumns = Schema::getColumnListing('tasks');

$tasks = Task::with('taskList')
    ->whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->orderBy('id')
    ->get();

foreach ($tasks as $task) {
    $list = $task->taskList?->name ?? '-';
    $code = preg_match('/^\[([^\]]+)\]/', $task->title, $m) ? $m[1] : 'NO-CODE-' . $task->id;
    $progress = (int) ($task->progress ?? 0);
    $start = $task->start_date ? substr((string) $task->start_date, 0, 10) : null;
    $due = $task->due_date ? substr((string) $task->due_date, 0, 10) : null;
    $duration = (int) ($task->duration ?? 0);
    $wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

    $summary[$list] = ($summary[$list] ?? 0) + 1;

    if (!$wbs) {
        $issues[] = [$code, 'WBS_MISSING', 'Card belum linked ke WBS'];
    }

    if (!$start || !$due) {
        $issues[] = [$code, 'DATE_MISSING', 'Start/due date card belum lengkap'];
    }

    if ($start && $due && $start > $due) {
        $issues[] = [$code, 'DATE_INVALID', "Start {$start} lebih besar dari due {$due}"];
    }

    if ($start && $due && $duration > 0) {
        $expectedDuration = (new DateTime($start))->diff(new DateTime($due))->days + 1;
        if ($duration !== $expectedDuration) {
            $issues[] = [$code, 'DURATION_MISMATCH', "Duration {$duration}, expected {$expectedDuration} untuk {$start} - {$due}"];
        }
    }

    if ($list === 'Ready' && $due && $due < $today) {
        $issues[] = [$code, 'READY_OVERDUE', "Ready tapi due {$due} sudah lewat {$today}"];
    }

    if ($wbs) {
        foreach (['start_date', 'due_date'] as $column) {
            if (in_array($column, $wbsColumns, true)) {
                $wbsDate = $wbs->{$column} ? substr((string) $wbs->{$column}, 0, 10) : null;
                $taskDate = $column === 'start_date' ? $start : $due;
                if ($wbsDate && $taskDate && $wbsDate !== $taskDate) {
                    $issues[] = [$code, 'WBS_DATE_MISMATCH', "{$column}: card {$taskDate}, WBS {$wbsDate}"];
                }
            }
        }

        foreach (['progress', 'status'] as $column) {
            if ($column === 'progress' && in_array($column, $wbsColumns, true)) {
                $wbsProgress = (int) ($wbs->{$column} ?? 0);
                if (abs($wbsProgress - $progress) > 0) {
                    $issues[] = [$code, 'WBS_PROGRESS_MISMATCH', "Card {$progress}%, WBS {$wbsProgress}%"];
                }
            }
        }
    }
}

echo json_encode([
    'board_id' => $boardId,
    'today' => $today,
    'task_count' => $tasks->count(),
    'summary_by_list' => $summary,
    'wbs_columns_checked' => array_values(array_intersect($wbsColumns, ['start_date', 'due_date', 'progress', 'status'])),
    'issue_count' => count($issues),
    'issues' => array_slice($issues, 0, 80),
], JSON_PRETTY_PRINT) . PHP_EOL;

exit($issues ? 1 : 0);
