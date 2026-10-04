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
$code = 'TAX-016';

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
            'title' => '[TAX-016] Kalender compliance pajak, reminder, dan eskalasi SLA',
            'target' => 'Admin payroll bisa membuat deadline compliance pajak PPh 21, Coretax, bukti potong, dan koreksi. Sistem menyimpan reminder H-n, owner, escalation PIC, SLA normal/high/critical, status, checklist compliance, notification snapshot, escalation snapshot, dan audit log.',
            'notes' => 'Status Testing 90%. Migration tax_compliance_events sudah ran. Test Laravel lulus 28 passed / 293 assertions. Terhubung ke card TAX-016, jadwal Gantt 27 Jul 2026.',
        ]);

    $user = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::firstOrFail();
    $comment = "Update progress: TAX-016 Kalender compliance pajak, reminder, dan eskalasi SLA siap Testing 90%.\n\n"
        . "Yang sudah dibuat: tabel tax_compliance_events, model TaxComplianceEvent, TaxComplianceCalendarController, halaman Kalender Compliance Pajak, route GET/POST payroll/tax-compliance-calendar, menu Payroll & Tax, event monthly PPh 21/annual/Coretax/bukti potong/koreksi/internal review, due date, reminder date H-n, SLA normal/high/critical, owner, escalation PIC, status planned/in progress/completed/overdue/escalated, checklist snapshot, notification snapshot, escalation snapshot, completed_at, escalated_at, dan audit log tax_compliance_event.created.\n\n"
        . "Verifikasi: migration 2026_07_12_000020 sudah Ran, test Laravel lulus 28 passed / 293 assertions. WBS dan Gantt disinkronkan ke target 27 Jul 2026.";

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
            'details' => 'TAX-016 moved to Testing, progress set from ' . $oldProgress . '% to 90%, WBS/Gantt synced.',
        ]);
    }
});

$task->refresh();
$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo "Updated TAX-016\n";
echo "Task: {$task->id} {$task->title}\n";
echo "List: {$task->taskList->name}\n";
echo "Progress: {$task->progress}%\n";
echo "Start: {$task->start_date} Due: {$task->due_date} Duration: {$task->duration}\n";
echo "WBS: " . ($wbs ? "{$wbs->id} {$wbs->title}" : 'not found') . "\n";
