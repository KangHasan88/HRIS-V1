<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$code = 'TAX-018';

$testingList = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[' . $code . ']%')
    ->firstOrFail();

$oldProgress = $task->progress ?? 0;

DB::transaction(function () use ($task, $testingList, $oldProgress, $boardId) {
    $task->forceFill([
        'task_list_id' => $testingList->id,
        'progress' => 90,
        'start_date' => '2026-07-27',
        'due_date' => '2026-07-27',
        'duration' => 1,
    ])->save();

    WbsItem::where('board_id', $boardId)
        ->where('task_id', $task->id)
        ->update([
            'title' => '[TAX-018] Regression test PPh 21 Indonesia dan integrasi Coretax',
            'target' => 'Admin payroll bisa menjalankan regression case PPh 21 Indonesia dan readiness Coretax. Sistem menyimpan input payload, expected result, actual result, variance, status pass/fail, schema Coretax, assertion snapshot, dan audit log.',
            'notes' => 'Status Testing 90%. Migration tax_regression_cases sudah ran. Test Laravel lulus 29 passed / 305 assertions. Terhubung ke card TAX-018, jadwal Gantt 27 Jul 2026.',
        ]);

    $user = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::firstOrFail();
    $comment = "Update progress: TAX-018 Regression test PPh 21 Indonesia dan integrasi Coretax siap Testing 90%.\n\n"
        . "Yang sudah dibuat: tabel tax_regression_cases, model TaxRegressionCase, TaxRegressionCaseController, halaman Regression Test Pajak, route GET/POST payroll/tax-regression-cases, menu Payroll & Tax, case type PPh 21 bulanan/tahunan/penghasilan tidak teratur/non permanen/Coretax template, expected vs actual PPh 21, variance, status pass/fail, schema Coretax, validation status, input/expected/actual payload, assertion snapshot, last_run_at, dan audit log tax_regression_case.executed.\n\n"
        . "Verifikasi: migration 2026_07_12_000021 sudah Ran, test Laravel lulus 29 passed / 305 assertions. WBS dan Gantt disinkronkan ke target 27 Jul 2026.";

    Comment::create([
        'content' => $comment,
        'user_id' => $user->id,
        'task_id' => $task->id,
        'mentions' => [],
        'likes_count' => 0,
    ]);

    if (class_exists(TaskActivity::class)) {
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action' => 'updated',
            'details' => 'TAX-018 moved to Testing, progress set from ' . $oldProgress . '% to 90%, WBS/Gantt synced.',
        ]);
    }
});

$task->refresh();
$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo "Updated TAX-018\n";
echo "Task: {$task->id} {$task->title}\n";
echo "List: {$task->taskList->name}\n";
echo "Progress: {$task->progress}%\n";
echo "Start: {$task->start_date} Due: {$task->due_date} Duration: {$task->duration}\n";
echo "WBS: " . ($wbs ? "{$wbs->id} {$wbs->title}" : 'not found') . "\n";
