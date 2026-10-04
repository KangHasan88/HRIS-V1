<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[RECRUI-060]%')
    ->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

if (Schema::hasColumn('tasks', 'progress') && (int) $task->progress < 92) {
    $task->progress = 92;
    $task->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'UI clarification: field angka kandidat sudah diperjelas menjadi Skor Screening (0-100), dengan helper bahwa nilai ini adalah score awal sebelum interview berdasarkan CV/profil dan kebutuhan posisi. Kolom tabel juga diganti menjadi Skor Screening agar tidak bias dengan skor interview/psikotest. Laravel test tetap hijau: 47 passed / 543 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'RECRUI-060',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
