<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[RECRUI-065]%')
    ->firstOrFail();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'UX refinement lintas form: ditambahkan komponen reusable field-help berupa icon bantuan di label field. Pop-up muncul saat hover/focus dan berisi penjelasan fungsi field serta cara pengisian. Sudah diterapkan pada form Talent Acquisition: Jadwal Interview, Offer Letter, Pengecekan Kandidat, dan Konversi Employee. Blade view:cache sukses dan Laravel test tetap 52 passed / 612 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'task_id' => $task->id,
    'title' => $task->title,
    'comment' => 'field help ux refinement added',
], JSON_PRETTY_PRINT) . PHP_EOL;
