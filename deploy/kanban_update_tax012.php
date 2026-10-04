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
$code = 'TAX-012';

$testingList = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[' . $code . ']%')
    ->firstOrFail();

$oldListId = $task->task_list_id;
$oldProgress = $task->progress ?? 0;

DB::transaction(function () use ($task, $testingList, $oldListId, $oldProgress, $boardId, $code) {
    $task->forceFill([
        'task_list_id' => $testingList->id,
        'progress' => 90,
        'start_date' => $task->start_date ?: '2026-07-21',
        'due_date' => '2026-07-23',
        'duration' => 3,
    ])->save();

    WbsItem::where('board_id', $boardId)
        ->where('task_id', $task->id)
        ->update([
            'title' => '[TAX-012] Generate e-Bupot/bukti potong dan download via ESS',
            'target' => 'Admin payroll bisa generate bukti potong dari paket laporan pajak bulanan, publish dokumen ke ESS, menyimpan snapshot karyawan, payload dokumen, checksum, status draft/published, dan audit log. Karyawan bisa melihat dan download metadata bukti potong melalui ESS WebView.',
            'notes' => 'Status Testing 90%. Migration tax_withholding_documents sudah ran. Test Laravel lulus 25 passed / 252 assertions. Terhubung ke card TAX-012, jadwal Gantt 23 Jul 2026.',
        ]);

    $user = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::firstOrFail();
    $comment = "Update progress: TAX-012 Generate e-Bupot/bukti potong dan download via ESS siap Testing 90%.\n\n"
        . "Yang sudah dibuat: tabel tax_withholding_documents, model TaxWithholdingDocument, TaxWithholdingDocumentController, halaman admin Bukti Potong, halaman Bukti Potong ESS, route payroll/tax-withholding-documents GET/POST, route ess/tax-documents, menu Payroll & Tax + ESS, generate dokumen dari paket laporan pajak bulanan, status draft/published, employee snapshot, document payload, checksum, published_at, dan audit log tax_withholding_document.generated.\n\n"
        . "Verifikasi: migration 2026_07_12_000017 sudah Ran, test Laravel lulus 25 passed / 252 assertions. WBS dan Gantt disinkronkan ke target 23 Jul 2026.";

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
            'details' => 'TAX-012 moved to Testing, progress set from ' . $oldProgress . '% to 90%, WBS/Gantt synced.',
        ]);
    }
});

$task->refresh();
$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo "Updated {$code}\n";
echo "Task: {$task->id} {$task->title}\n";
echo "List: {$task->taskList->name}\n";
echo "Progress: {$task->progress}%\n";
echo "Start: {$task->start_date} Due: {$task->due_date} Duration: {$task->duration}\n";
echo "WBS: " . ($wbs ? "{$wbs->id} {$wbs->title}" : 'not found') . "\n";
