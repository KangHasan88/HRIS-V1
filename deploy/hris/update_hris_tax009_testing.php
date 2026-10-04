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
    ->where('title', 'like', '[TAX-009]%')
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
    'content' => 'Update progress: TAX-009 Generator template ekspor Coretax dan versioning schema siap testing. Sudah dibuat tabel tax_monthly_export_templates, model TaxMonthlyExportTemplate, relasi ke TaxMonthlyReportPackage, route POST payroll/tax-report-packages/{package}/templates, generator template number, schema version coretax-v1.0/v1.1, format CSV/XLSX/JSON, status generated/validated/ready_to_export, columns_schema, payload_snapshot, checksum SHA-256, daftar Template Ekspor Coretax di UI, dan audit log tax_monthly_export_template.generated. Migration sukses, Laravel test 23 passed / 209 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'TAX-009',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
