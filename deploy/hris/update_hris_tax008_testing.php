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
    ->where('title', 'like', '[TAX-008]%')
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
    'content' => 'Update progress: TAX-008 Paket laporan pajak bulanan siap Coretax masuk testing. Sudah dibuat tabel tax_monthly_report_packages, model TaxMonthlyReportPackage, controller, halaman Laporan Pajak Bulanan, route GET/POST payroll/tax-report-packages, menu Payroll & Tax, generator package number, rekap employee count/gross/tax base/PPh21, detail snapshot per karyawan, summary tax method, validation notes, status draft/final/ready_for_coretax, dan audit log tax_monthly_report_package.created. Migration sukses, Laravel test 23 passed / 200 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'TAX-008',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
