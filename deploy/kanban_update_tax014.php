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
$code = 'TAX-014';

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
            'title' => '[TAX-014] Sandbox simulasi pajak sebelum finalisasi payroll',
            'target' => 'Admin payroll bisa menjalankan scenario simulasi PPh 21 tanpa mengubah data final payroll. Sistem menyimpan input scenario, hasil simulasi, comparison terhadap kalkulasi final, metode pajak, status review, dan audit log.',
            'notes' => 'Status Testing 90%. Migration tax_simulations sudah ran. Test Laravel lulus 27 passed / 279 assertions. Terhubung ke card TAX-014, jadwal Gantt 27 Jul 2026.',
        ]);

    $user = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::firstOrFail();
    $comment = "Update progress: TAX-014 Sandbox simulasi pajak sebelum finalisasi payroll siap Testing 90%.\n\n"
        . "Yang sudah dibuat: tabel tax_simulations, model TaxSimulation, TaxSimulationController, halaman Sandbox Simulasi Pajak, route GET/POST payroll/tax-simulations, menu Payroll & Tax, scenario simulation sebelum payroll final, metode gross/gross-up/net/company borne/tax allowance, perhitungan TER, take home pay, company tax borne, input/result/comparison snapshot, delta terhadap payroll final, status draft/ready/rejected/approved, dan audit log tax_simulation.created.\n\n"
        . "Verifikasi: migration 2026_07_12_000019 sudah Ran, test Laravel lulus 27 passed / 279 assertions. WBS dan Gantt disinkronkan ke target 27 Jul 2026.";

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
            'details' => 'TAX-014 moved to Testing, progress set from ' . $oldProgress . '% to 90%, WBS/Gantt synced.',
        ]);
    }
});

$task->refresh();
$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo "Updated TAX-014\n";
echo "Task: {$task->id} {$task->title}\n";
echo "List: {$task->taskList->name}\n";
echo "Progress: {$task->progress}%\n";
echo "Start: {$task->start_date} Due: {$task->due_date} Duration: {$task->duration}\n";
echo "WBS: " . ($wbs ? "{$wbs->id} {$wbs->title}" : 'not found') . "\n";
