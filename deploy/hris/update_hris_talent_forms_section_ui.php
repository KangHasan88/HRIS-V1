<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$prefixes = [
    'RECRUI-060',
    'RECRUI-061',
    'RECRUI-062',
    'RECRUI-063',
    'RECRUI-064',
    'RECRUI-065',
    'RECRUI-066',
];

$content = 'UI refinement lintas Talent Acquisition: form Manpower Request, Tambah Kandidat, Portal Kandidat manual, Jadwal Interview, Offer Letter, Pengecekan Kandidat, dan Konversi Employee disamakan dengan pattern Welcome Journey. Form sekarang memakai section card, label field eksplisit, placeholder berupa contoh, grouping yang mudah dibaca saat kosong, dan satu form-level help icon untuk penjelasan cara pakai. Blade view:cache sukses dan Laravel test tetap 53 passed / 625 assertions.';

$updated = [];

foreach ($prefixes as $prefix) {
    $task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
        ->where('title', 'like', '[' . $prefix . ']%')
        ->first();

    if (! $task) {
        continue;
    }

    Comment::create([
        'task_id' => $task->id,
        'user_id' => $actor?->id,
        'content' => $content,
        'mentions' => [],
        'likes_count' => 0,
    ]);

    $updated[] = [
        'task_id' => $task->id,
        'title' => $task->title,
    ];
}

echo json_encode([
    'updated_count' => count($updated),
    'updated' => $updated,
], JSON_PRETTY_PRINT) . PHP_EOL;
