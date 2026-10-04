<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[PLATFO-001]%')
    ->firstOrFail();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 80;
    $task->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: halaman Organization Scope sudah dibuat di menu Administrasi Data. Admin dapat melihat ringkasan perusahaan, badan hukum, cabang/site, user per cabang, dan scope user di User Access. Route settings/organization terdaftar, view cache sukses, Laravel test 11 passed. Sisa: CRUD master organisasi dan enforcement scope per modul/query.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'PLATFO-001',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
