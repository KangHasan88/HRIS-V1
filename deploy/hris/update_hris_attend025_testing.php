<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[ATTEND-025]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: ATTEND-025 Mesin kebijakan cuti siap testing. Sudah dibuat tabel leave_policy_types dan leave_policy_rules, model LeavePolicyType/LeavePolicyRule, controller LeavePolicyController, halaman Kebijakan Cuti dengan pattern form mini-card seperti Jadwal Shift, route GET/POST leave/policies, menu Operasional HR > Kebijakan Cuti, master jenis cuti tahunan/sakit/khusus/unpaid/melahirkan, kuota tahunan, max per pengajuan, paid/unpaid, wajib saldo/lampiran/approval, carry forward max hari + expiry, prorata join date, gender rule, minimum masa kerja, aturan scope company/branch/status karyawan, accrual frequency, reset month, opsi hari libur/weekend dihitung, dan audit log type + rule. Route smoke sukses dan Laravel test 58 passed / 696 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-025',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
