<?php

use App\Models\Task;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$workedCodes = [
    'DAY-01', 'DAY-02', 'DAY-03', 'DAY-04',
    'PLATFO-001', 'PLATFO-002', 'PLATFO-003', 'PLATFO-004', 'PLATFO-005',
    'ADMIN-001', 'AUTH-002',
    'HRCORE-012', 'HRCORE-013', 'HRCORE-014', 'HRCORE-015', 'HRCORE-016', 'HRCORE-017', 'HRCORE-018', 'HRCORE-019', 'HRCORE-020',
    'ATTEND-021', 'ATTEND-022', 'ATTEND-023', 'ATTEND-024', 'ATTEND-025', 'ATTEND-026', 'ATTEND-029',
    'ESSMOB-045', 'MOBILE-004',
    'RECRUI-059', 'RECRUI-060', 'RECRUI-061', 'RECRUI-062', 'RECRUI-063', 'RECRUI-064', 'RECRUI-065', 'RECRUI-066',
    'PAYROL-033',
    'TAX-001', 'TAX-002', 'TAX-003', 'TAX-004', 'TAX-005', 'TAX-006', 'TAX-007', 'TAX-008', 'TAX-009', 'TAX-010', 'TAX-011', 'TAX-015', 'TAX-017',
    'COMPLI-094', 'INTEGR-090',
];

$rows = [];
$issues = [];

foreach ($workedCodes as $code) {
    $task = Task::with(['taskList', 'comments'])
        ->whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
        ->where('title', 'like', '[' . $code . ']%')
        ->first();

    if (! $task) {
        $issues[] = [
            'code' => $code,
            'issue' => 'task_not_found',
        ];
        continue;
    }

    $list = $task->taskList?->name ?: '-';
    $progress = (int) ($task->progress ?? 0);
    $comments = $task->comments->pluck('content')->implode("\n");
    $hasTestingComment = Str::contains(Str::lower($comments), ['siap testing', 'full laravel test', 'laravel test', 'smoke']);
    $hasInProgressComment = Str::contains(Str::lower($comments), ['in progress', 'mulai', 'sedang']);
    $wbs = \App\Models\WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

    $row = [
        'code' => $code,
        'task_id' => $task->id,
        'list' => $list,
        'progress' => $progress,
        'wbs' => $wbs?->code,
        'has_testing_comment' => $hasTestingComment,
        'has_in_progress_comment' => $hasInProgressComment,
    ];
    $rows[] = $row;

    if ($hasTestingComment && $list !== 'Testing') {
        $issues[] = $row + ['issue' => 'has_testing_comment_but_not_testing'];
    }

    if ($progress >= 80 && $list !== 'Testing') {
        $issues[] = $row + ['issue' => 'high_progress_but_not_testing'];
    }

    if ($list === 'Testing' && $progress < 80) {
        $issues[] = $row + ['issue' => 'testing_with_low_progress'];
    }

    if (! $wbs) {
        $issues[] = $row + ['issue' => 'missing_wbs_link'];
    }
}

echo json_encode([
    'board_id' => $boardId,
    'checked_count' => count($workedCodes),
    'found_count' => count($rows),
    'issue_count' => count($issues),
    'issues' => $issues,
    'by_list' => collect($rows)->groupBy('list')->map->count()->sortKeys(),
    'rows' => $rows,
], JSON_PRETTY_PRINT) . PHP_EOL;
