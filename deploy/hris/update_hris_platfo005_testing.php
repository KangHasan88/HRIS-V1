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
    ->where('title', 'like', '[PLATFO-005]%')
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
    'content' => 'Update progress: audit trail platform sudah dibuat. Tabel audit_logs, model AuditLog, helper AuditLogger, halaman Audit Trail, menu sidebar, dan logging untuk role, permission, user access, organisasi, serta module switcher per company sudah berjalan. Migration sukses, route settings/audit-trail terdaftar, Laravel test 13 passed. Catatan lanjutan: filter advanced/export dan audit domain payroll akan dilanjutkan di TAX/COMPLI card terkait.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'PLATFO-005',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
