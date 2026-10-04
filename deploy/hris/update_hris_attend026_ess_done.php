<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[ATTEND-026]%')
    ->firstOrFail();

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'ESS flow completed for ATTEND-026: sudah ditambahkan controller EssLeaveRequestController, halaman ESS Cuti Saya, route GET/POST ess/leave-requests, menu Employee Self Service > Cuti Saya, simulasi saldo employee sendiri, submit request dari ESS WebView/Mobile WebView, status otomatis Submitted, current approver Atasan langsung, riwayat request hanya milik user login, dan audit log ess_leave_request.submitted. Akses ESS dibatasi dengan auth + employee_profile user_id/work_email, bukan pilihan employee manual. Route smoke sukses dan full Laravel test 60 passed / 721 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-026',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
