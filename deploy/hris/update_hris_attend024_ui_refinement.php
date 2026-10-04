<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[ATTEND-024]%')
    ->firstOrFail();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'UI refinement: form Import Absensi disamakan dengan pattern Jadwal Shift. Section Data Batch, Periode & Source, Identitas Row, dan Event Absensi sekarang memakai mini-card rounded border p-4, spacing form dibuat lebih konsisten, grid disesuaikan, textarea non-resize, dan tombol action dibuat full-width seperti form shift agar area submit lebih jelas. Blade cache sukses dan smoke test ATTEND-024 passed.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-024',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
