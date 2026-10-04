<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;
$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[ESSMOB-042]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
if ($wbs && Schema::hasColumn('wbs_items', 'progress')) {
    $wbs->progress = 90;
}
if ($wbs && Schema::hasColumn('wbs_items', 'status')) {
    $wbs->status = 'Testing';
}
if ($wbs) {
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: ESSMOB-042 Login biometric mobile, fallback PIN, dan trusted device siap testing. Sudah dibuat tabel ess_trusted_devices, model EssTrustedDevice, controller EssSecurityController, halaman ESS Security, route GET/POST settings/ess-security, menu Employee Self Service > ESS Security, registry device WebView Android/iOS/browser, biometric enabled/type, fallback PIN hash, trust status pending/trusted/blocked/revoked, failed attempts, risk flags biometric_disabled/pin_missing/browser_trusted_device/multiple_failed_attempts, capability_snapshot untuk biometric.verify, update Mobile Bridge biometric.verify menjadi Ready, dan audit log ess_trusted_device.saved. Route smoke sukses dan full Laravel test passed.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ESSMOB-042',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
    'wbs_progress' => $wbs?->progress ?? null,
    'wbs_status' => $wbs?->status ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
