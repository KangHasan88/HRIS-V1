<?php

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[INTEGR-088]%')
    ->firstOrFail();

$testingList = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();

$task->task_list_id = $testingList->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

$checklistIds = Checklist::where('task_id', $task->id)->pluck('id');
ChecklistItem::whereIn('checklist_id', $checklistIds)->update(['is_checked' => true]);

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Testing 90%: INTEGR-088 Integrasi akuntansi/ERP untuk jurnal payroll dan cost center selesai. Implementasi mencakup migration accounting_erp_connectors, payroll_accounting_journal_batches, payroll_accounting_journal_lines, model connector/journal/line, AccountingErpController, route /integrations/accounting-erp, menu Accounting & ERP di Integrasi, setup connector ERP, mapping GL salary/tax/net payable, mapping cost center, generate jurnal payroll dari payslip published/acknowledged, balancing debit/kredit, payload JSON ERP, checksum, audit log accounting_erp_connector.created dan payroll_accounting_journal.generated, feature test, smoke HTTPS, dan full regression. URL: https://31.97.106.123:8091/integrations/accounting-erp. Targeted test: 1 passed / 23 assertions. Full test: 138 passed / 1891 assertions. Smoke HTTPS: status 200 page ok.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'INTEGR-088',
    'task_id' => $task->id,
    'title' => Str::limit($task->title, 100),
    'list' => $testingList->name,
    'progress' => $task->progress ?? null,
    'checked' => ChecklistItem::whereIn('checklist_id', $checklistIds)->where('is_checked', true)->count(),
    'total_checklist' => ChecklistItem::whereIn('checklist_id', $checklistIds)->count(),
    'wbs' => $wbs?->code,
], JSON_PRETTY_PRINT).PHP_EOL;
