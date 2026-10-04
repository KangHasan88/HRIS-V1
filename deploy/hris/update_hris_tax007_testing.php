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
    ->where('title', 'like', '[TAX-007]%')
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
    'content' => 'Update progress: TAX-007 Mapping komponen kena pajak dan dependency formula payroll siap testing. Sudah dibuat tabel payroll_tax_components, model PayrollTaxComponent, route POST payroll/tax-monthly/components, form Mapping Komponen Pajak di halaman Hitung PPh 21 Bulanan, pilihan komponen payroll saat kalkulasi, mapping taxable_income/non_taxable/deduction/company_cost, formula key, dependency list, default amount, dan audit log payroll_tax_component.created. Engine PPh 21 bulanan sekarang memasukkan komponen taxable/pengurang/company cost ke calculation steps dan source snapshot. Migration sukses, Laravel test 22 passed / 187 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'TAX-007',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
