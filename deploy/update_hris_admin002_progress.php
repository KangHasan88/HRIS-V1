<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[ADMIN-002]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 80;
}
$task->save();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: Module Switcher sudah dibuat dengan tabel MySQL module_settings, seeder modul, halaman admin /settings/modules, toggle aktif/nonaktif, dashboard membaca status modul dari database, dan smoke test login + toggle berhasil. Sisa berikutnya: scope per-company setelah tenancy aktif.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ADMIN-002',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
