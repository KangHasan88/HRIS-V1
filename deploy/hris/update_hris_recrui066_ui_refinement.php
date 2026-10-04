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
    ->where('title', 'like', '[RECRUI-066]%')
    ->firstOrFail();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'UI refinement: form Welcome Journey dirapikan menjadi section Data Employee, Pendamping Onboarding, Periode & Probation, Checklist Onboarding Awal, Target Probation, Agenda Welcome, dan Catatan Journey. Istilah Buddy di UI diganti menjadi Pendamping Onboarding agar lebih jelas untuk user Indonesia. Date field diberi label eksplisit Mulai Onboarding dan Akhir Probation. Blade view:cache sukses dan Laravel test tetap 53 passed / 625 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'task_id' => $task->id,
    'title' => $task->title,
    'comment' => 'welcome journey ui refinement added',
], JSON_PRETTY_PRINT) . PHP_EOL;
