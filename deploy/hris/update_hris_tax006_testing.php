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
    ->where('title', 'like', '[TAX-006]%')
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
    'content' => 'Update progress: TAX-006 Mode gross-up, gross, net, pajak ditanggung perusahaan, dan tunjangan pajak siap testing. Mesin PPh 21 bulanan sekarang menyimpan tax_method, tax_allowance_amount, company_tax_borne_amount, dan take_home_pay. Form Hitung Bulanan punya opsi Gross, Gross-up, Net, Pajak ditanggung perusahaan, dan Tunjangan pajak; tabel hasil menampilkan metode, dasar TER, tunjangan pajak, pajak perusahaan, PPh 21, dan THP. Logic per mode masuk ke calculation steps dan audit log tax_monthly.calculated. Migration sukses, Laravel test 22 passed / 179 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'TAX-006',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
