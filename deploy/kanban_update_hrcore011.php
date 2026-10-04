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
$code = 'HRCORE-011';

$testingList = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[' . $code . ']%')
    ->firstOrFail();

$oldProgress = $task->progress ?? 0;

DB::transaction(function () use ($task, $testingList, $oldProgress, $boardId) {
    $task->forceFill([
        'task_list_id' => $testingList->id,
        'progress' => 90,
        'start_date' => '2026-07-15',
        'due_date' => '2026-07-15',
        'duration' => 1,
    ])->save();

    WbsItem::where('board_id', $boardId)
        ->where('task_id', $task->id)
        ->update([
            'title' => '[HRCORE-011] Profil karyawan 360: pekerjaan, keluarga, bank, pajak, BPJS',
            'target' => 'Admin HR bisa membuat profil karyawan 360 yang menghubungkan user login, company, branch, data pekerjaan, keluarga, rekening bank, profil pajak PPh 21, BPJS, kontak, alamat, status kerja, dan audit log.',
            'notes' => 'Status Testing 90%. Migration employee_profiles, employee_family_members, employee_bank_accounts, dan employee_bpjs_profiles sudah ran. Test Laravel lulus 32 passed / 350 assertions. Terhubung ke card HRCORE-011, jadwal Gantt 15 Jul 2026.',
        ]);

    $user = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::firstOrFail();
    $comment = "Update progress: HRCORE-011 Profil karyawan 360 siap Testing 90%.\n\n"
        . "Yang sudah dibuat: tabel employee_profiles, employee_family_members, employee_bank_accounts, employee_bpjs_profiles; model EmployeeProfile, EmployeeFamilyMember, EmployeeBankAccount, EmployeeBpjsProfile; EmployeeProfileController; halaman Data Karyawan 360; route GET/POST /employees; link user/company/branch/tax profile; data pekerjaan, status kerja, grade, cost center, join date, kontrak, kontak, alamat, keluarga, bank, BPJS, document summary, dan audit log employee_profile.created.\n\n"
        . "Verifikasi: migration 2026_07_12_000024 sudah Ran, test Laravel lulus 32 passed / 350 assertions. WBS dan Gantt disinkronkan ke target 15 Jul 2026.";

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
            'details' => 'HRCORE-011 moved to Testing, progress set from ' . $oldProgress . '% to 90%, WBS/Gantt synced.',
        ]);
    }
});

$task->refresh();
$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

echo "Updated HRCORE-011\n";
echo "Task: {$task->id} {$task->title}\n";
echo "List: {$task->taskList->name}\n";
echo "Progress: {$task->progress}%\n";
echo "Start: {$task->start_date} Due: {$task->due_date} Duration: {$task->duration}\n";
echo "WBS: " . ($wbs ? "{$wbs->id} {$wbs->title}" : 'not found') . "\n";
