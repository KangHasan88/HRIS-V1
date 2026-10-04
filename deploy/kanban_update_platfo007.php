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
$code = 'PLATFO-007';

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
            'title' => '[PLATFO-007] Pusat notifikasi: email, WhatsApp, push, dan in-app',
            'target' => 'Admin bisa membuat template notifikasi multi-channel dan queue delivery untuk user/manual recipient. Sistem menyimpan channel in-app/email/WhatsApp/push, subject/body template, variables schema, rendered message, payload snapshot, delivery log, priority, schedule, status, dan audit log.',
            'notes' => 'Status Testing 90%. Migration notification_templates dan notification_deliveries sudah ran. Test Laravel lulus 31 passed / 337 assertions. Terhubung ke card PLATFO-007, jadwal Gantt 14 Jul 2026.',
        ]);

    $user = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::firstOrFail();
    $comment = "Update progress: PLATFO-007 Pusat notifikasi email, WhatsApp, push, dan in-app siap Testing 90%.\n\n"
        . "Yang sudah dibuat: tabel notification_templates dan notification_deliveries, model NotificationTemplate dan NotificationDelivery, NotificationCenterController, halaman Pusat Notifikasi, route GET settings/notification-center, route POST templates dan deliveries, menu System Administration, template event key, channel in-app/email/WhatsApp/push, subject/body template dengan variable rendering, variables schema, queue delivery ke user/manual recipient, priority, scheduled_at, payload snapshot, delivery log provider waiting_worker, status queued, dan audit log notification_template.created serta notification_delivery.queued.\n\n"
        . "Verifikasi: migration 2026_07_12_000023 sudah Ran, test Laravel lulus 31 passed / 337 assertions. WBS dan Gantt disinkronkan ke target 14 Jul 2026.";

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
            'details' => 'PLATFO-007 moved to Testing, progress set from ' . $oldProgress . '% to 90%, WBS/Gantt synced.',
        ]);
    }
});

$task->refresh();
$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo "Updated PLATFO-007\n";
echo "Task: {$task->id} {$task->title}\n";
echo "List: {$task->taskList->name}\n";
echo "Progress: {$task->progress}%\n";
echo "Start: {$task->start_date} Due: {$task->due_date} Duration: {$task->duration}\n";
echo "WBS: " . ($wbs ? "{$wbs->id} {$wbs->title}" : 'not found') . "\n";
