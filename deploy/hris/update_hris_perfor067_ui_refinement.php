<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', 6))
    ->where('title', 'like', '[PERFOR-067]%')
    ->firstOrFail();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'UI refinement: halaman Goal & OKR dirapikan. Form tidak lagi dipaksa 3 kolom penuh; sekarang dibuat alur vertikal 1) Siklus OKR, 2) Goal/Key Result, 3) Check-in Progress dengan label field jelas, spacing lebih lega, tombol di kanan bawah tiap form, dan panel kanan untuk Urutan Pakai, Quick Insight, serta Catatan UX. Targeted test PERFOR-067 pass. Smoke HTTPS: status 200.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'task_id' => $task->id,
    'title' => $task->title,
    'comment' => 'ui refinement added',
], JSON_PRETTY_PRINT).PHP_EOL;
