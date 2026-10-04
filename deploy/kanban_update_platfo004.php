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
$code = 'PLATFO-004';

$testingList = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[' . $code . ']%')
    ->firstOrFail();

$oldProgress = $task->progress ?? 0;

DB::transaction(function () use ($task, $testingList, $oldProgress, $boardId) {
    $task->forceFill([
        'task_list_id' => $testingList->id,
        'progress' => 90,
        'start_date' => '2026-07-14',
        'due_date' => '2026-07-14',
        'duration' => 1,
    ])->save();

    WbsItem::where('board_id', $boardId)
        ->where('task_id', $task->id)
        ->update([
            'title' => '[PLATFO-004] Builder workflow approval dengan routing kondisional',
            'target' => 'Admin bisa membuat workflow approval lintas modul dengan trigger event, condition schema, fallback approver, step berurutan/paralel, approver type, SLA, delegasi, notification channel, status publish, dan audit log.',
            'notes' => 'Status Testing 90%. Migration approval_workflows dan approval_workflow_steps sudah ran. Test Laravel lulus 30 passed / 319 assertions. Terhubung ke card PLATFO-004, jadwal Gantt 14 Jul 2026.',
        ]);

    $user = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::firstOrFail();
    $comment = "Update progress: PLATFO-004 Builder workflow approval dengan routing kondisional siap Testing 90%.\n\n"
        . "Yang sudah dibuat: tabel approval_workflows dan approval_workflow_steps, model ApprovalWorkflow dan ApprovalWorkflowStep, ApprovalWorkflowController, halaman Workflow Approval, route GET/POST settings/approval-workflows, menu System Administration, workflow per company/module, trigger event, condition schema, fallback route, step approval berurutan/paralel, approver type role/user/manager/branch head/HR/payroll, SLA hours, delegasi, notification channels, status draft/published/archived, enable/disable, dan audit log approval_workflow.created.\n\n"
        . "Verifikasi: migration 2026_07_12_000022 sudah Ran, test Laravel lulus 30 passed / 319 assertions. WBS dan Gantt disinkronkan ke target 14 Jul 2026.";

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
            'details' => 'PLATFO-004 moved to Testing, progress set from ' . $oldProgress . '% to 90%, WBS/Gantt synced.',
        ]);
    }
});

$task->refresh();
$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo "Updated PLATFO-004\n";
echo "Task: {$task->id} {$task->title}\n";
echo "List: {$task->taskList->name}\n";
echo "Progress: {$task->progress}%\n";
echo "Start: {$task->start_date} Due: {$task->due_date} Duration: {$task->duration}\n";
echo "WBS: " . ($wbs ? "{$wbs->id} {$wbs->title}" : 'not found') . "\n";
