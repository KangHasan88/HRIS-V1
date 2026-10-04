<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[RECRUI-061]%')
    ->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'UI refinement: layout Portal Kandidat admin disesuaikan agar flow utama menjadi Kandidat & Link Portal di kiri dan Review Dokumen & Link Portal di kanan. Form Catat Dokumen Manual dipindah menjadi collapsible fallback di bawah, sehingga tidak lagi dominan di first viewport. Laravel test 48 passed / 562 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'RECRUI-061',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
