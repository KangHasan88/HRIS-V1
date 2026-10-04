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
    ->where('title', 'like', '[TAX-002]%')
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
    'content' => 'Update progress: TAX-002 Mesin hitung PPh 21 bulanan TER siap testing. Sudah dibuat tabel tax_ter_rates dan tax_monthly_calculations, model TaxTerRate/TaxMonthlyCalculation, controller, halaman Hitung PPh 21 Bulanan, route GET/POST payroll/tax-monthly, menu Payroll & Tax, master tarif TER configurable kategori A/B/C, mapping PTKP ke kategori TER, lookup bracket berdasarkan dasar TER, kalkulasi PPh 21 bulanan, calculation steps, snapshot profil, dan audit log tax_ter_rate.created + tax_monthly.calculated. Migration sukses, route terdaftar, Laravel test 19 passed / 140 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'TAX-002',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;

