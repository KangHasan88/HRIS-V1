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
    ->where('title', 'like', '[TAX-004]%')
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
    'content' => 'Update progress: TAX-004 Perlakuan pajak THR, bonus, penghasilan tidak teratur, dan pajak final siap testing. Sudah dibuat tabel tax_irregular_incomes, model TaxIrregularIncome, controller, halaman Penghasilan Tidak Teratur, route GET/POST payroll/tax-irregular, menu Payroll & Tax, tipe THR/bonus/komisi/tunjangan tidak teratur/pajak final, metode gross/gross-up/net/final, rate configurable, hitung pajak dan net diterima, calculation steps, snapshot profil, dan audit log tax_irregular_income.calculated. Migration sukses, route terdaftar, Laravel test 21 passed / 159 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'TAX-004',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;

