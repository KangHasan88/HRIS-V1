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
    'content' => "Scope refinement: Pengajuan cuti wajib tersedia dari sisi karyawan/ESS juga, bukan hanya HR/Admin. Tambahan scope untuk ATTEND-026:\n- ESS employee bisa submit pengajuan cuti sendiri via WebView/mobile.\n- Employee hanya bisa melihat request miliknya sendiri.\n- ESS menampilkan simulasi saldo sebelum submit memakai policy engine yang sama.\n- ESS mendukung alasan cuti, referensi lampiran, dan source ess_webview/mobile_webview.\n- Employee bisa memantau status approval, approver saat ini, saldo sebelum/sesudah, dan catatan keputusan.\n- HR/Admin tetap punya mode input manual dan monitoring semua request.\nCatatan: admin flow sudah selesai testing; ESS flow perlu dibuat sebagai kelanjutan ATTEND-026 sebelum card dianggap complete.",
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ATTEND-026',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
