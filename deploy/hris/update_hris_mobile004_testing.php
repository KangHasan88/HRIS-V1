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
    ->where('title', 'like', '[MOBILE-004]%')
    ->firstOrFail();

$testing = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();
$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

$task->task_list_id = $testing->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

$wbs = WbsItem::where('board_id', $boardId)->where('task_id', $task->id)->first();
if ($wbs) {
    $wbsColumns = Schema::getColumnListing('wbs_items');
    if (in_array('progress', $wbsColumns, true)) {
        $wbs->progress = 90;
    }
    if (in_array('status', $wbsColumns, true)) {
        $wbs->status = 'Testing';
    }
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: MOBILE-004 sudah masuk Testing. Implementasi mencakup JavaScript SDK KurmigoBridge di public/js, deteksi host Android/iOS/React Native/browser, request contract berbasis capability, fallback browser aman, response event handler, halaman admin Mobile WebView Bridge, capability contract device/location/selfie/download/biometric/badge, menu ESS, route settings/mobile-bridge, dan test Laravel untuk halaman + file SDK. Laravel test 45 passed / 521 assertions.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'MOBILE-004',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
    'wbs_progress' => $wbs?->progress ?? null,
    'wbs_status' => $wbs?->status ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
