<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[RECRUI-060]%')
    ->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'UI refinement: field Sumber Kandidat sudah disejajarkan dengan field Skor Screening. Keduanya sekarang punya label, control, dan helper text seimbang agar select Direct tidak melebar/tinggi sendiri. Test recruitment passed: 2 passed / 22 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'RECRUI-060',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
