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
    ->where('title', 'like', '[PLATFO-003]%')
    ->firstOrFail();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 92;
    $task->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update feedback UX: User Access tidak lagi membuka select role/scope dan tombol Simpan secara default. Row utama sekarang read-only dengan tombol Edit; form role/company/legal entity/branch baru muncul saat Edit diklik, lengkap dengan Simpan dan Batal. Laravel test 12 passed.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'PLATFO-003',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->value('code'),
], JSON_PRETTY_PRINT) . PHP_EOL;
