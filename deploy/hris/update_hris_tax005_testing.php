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
    ->where('title', 'like', '[TAX-005]%')
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
    'content' => 'Update progress: TAX-005 Aturan PPh 21 pegawai tidak tetap/harian/freelancer/konsultan siap testing. Sudah dibuat tabel tax_non_permanent_workers dan tax_non_permanent_calculations, model TaxNonPermanentWorker/TaxNonPermanentCalculation, controller, halaman PPh 21 Non Permanen, route GET/POST payroll/tax-non-permanent, menu Payroll & Tax, master worker harian/freelancer/konsultan/bukan pegawai, kalkulasi gross/taxable base/rate/PPh 21, calculation steps, snapshot worker, dan audit log tax_non_permanent.worker_created + tax_non_permanent.calculated. Migration sukses, route terdaftar, Laravel test 22 passed / 174 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'TAX-005',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
