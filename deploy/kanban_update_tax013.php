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
$code = 'TAX-013';

$testingList = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[' . $code . ']%')
    ->firstOrFail();

$oldProgress = $task->progress ?? 0;

DB::transaction(function () use ($task, $testingList, $oldProgress, $boardId) {
    $task->forceFill([
        'task_list_id' => $testingList->id,
        'progress' => 90,
        'start_date' => '2026-07-24',
        'due_date' => '2026-07-24',
        'duration' => 1,
    ])->save();

    WbsItem::where('board_id', $boardId)
        ->where('task_id', $task->id)
        ->update([
            'title' => '[TAX-013] Koreksi pajak, pembetulan pelaporan, dan penyesuaian payroll retroaktif',
            'target' => 'Admin payroll bisa membuat koreksi PPh 21 untuk pembetulan pelaporan, koreksi payroll retroaktif, refund, dan kurang bayar. Sistem menyimpan nilai sebelum/sesudah, delta PPh 21, dampak payroll berikutnya, status approval, snapshot audit, dan link ke paket laporan pajak bulanan.',
            'notes' => 'Status Testing 90%. Migration tax_corrections sudah ran. Test Laravel lulus 26 passed / 265 assertions. Terhubung ke card TAX-013, jadwal Gantt 24 Jul 2026.',
        ]);

    $user = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::firstOrFail();
    $comment = "Update progress: TAX-013 Koreksi pajak, pembetulan pelaporan, dan penyesuaian payroll retroaktif siap Testing 90%.\n\n"
        . "Yang sudah dibuat: tabel tax_corrections, model TaxCorrection, TaxCorrectionController, halaman Koreksi Pajak, route GET/POST payroll/tax-corrections, menu Payroll & Tax, link ke paket laporan pajak bulanan, jenis koreksi pembetulan/retroaktif/refund/kurang bayar, nilai sebelum-sesudah, delta PPh 21, tipe adjustment payroll, status approval, before/after/workflow snapshot, approved_at, approved_by, dan audit log tax_correction.created.\n\n"
        . "Verifikasi: migration 2026_07_12_000018 sudah Ran, test Laravel lulus 26 passed / 265 assertions. WBS dan Gantt disinkronkan ke target 24 Jul 2026.";

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
            'details' => 'TAX-013 moved to Testing, progress set from ' . $oldProgress . '% to 90%, WBS/Gantt synced.',
        ]);
    }
});

$task->refresh();
$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo "Updated TAX-013\n";
echo "Task: {$task->id} {$task->title}\n";
echo "List: {$task->taskList->name}\n";
echo "Progress: {$task->progress}%\n";
echo "Start: {$task->start_date} Due: {$task->due_date} Duration: {$task->duration}\n";
echo "WBS: " . ($wbs ? "{$wbs->id} {$wbs->title}" : 'not found') . "\n";
