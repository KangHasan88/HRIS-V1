<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[ANALYT-081]%')
    ->firstOrFail();

$inProgressList = TaskList::where('board_id', $boardId)->where('name', 'In Progress')->firstOrFail();

$task->task_list_id = $inProgressList->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = max((int) $task->progress, 15);
}
$task->save();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Mulai coding ANALYT-081: Asisten dokumen HR untuk Q&A kebijakan dan draft surat karyawan. Scope implementasi: halaman assistant dokumen, knowledge base kebijakan, pencarian/Q&A berbasis keyword transparan, draft surat HR, rekomendasi template, route/menu, feature test, smoke HTTPS, lalu sync Kanban/WBS/Gantt.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ANALYT-081',
    'task_id' => $task->id,
    'title' => Str::limit($task->title, 100),
    'list' => $inProgressList->name,
    'progress' => $task->progress ?? null,
], JSON_PRETTY_PRINT).PHP_EOL;
