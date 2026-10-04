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
    ->where('title', 'like', '[ANALYT-083]%')
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
    $columns = Schema::getColumnListing('wbs_items');
    if (in_array('progress', $columns, true)) {
        $wbs->progress = 90;
    }
    if (in_array('status', $columns, true)) {
        $wbs->status = 'Testing';
    }
    $wbs->save();
}

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Update progress: ANALYT-083 masuk Testing. Implementasi mencakup tabel leave_overtime_capacity_predictions, model LeaveOvertimeCapacityPrediction, controller LeaveOvertimeCapacityController, halaman Prediksi Kapasitas Cuti & Lembur, perhitungan workday, kapasitas tersedia, gap kapasitas, estimasi jam lembur tambahan, risk score/level, rekomendasi tindakan, audit trail leave_overtime_capacity_prediction.created, menu Analytics & AI, route analytics/leave-overtime-capacity, dan test Laravel.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'ANALYT-083',
    'task_id' => $task->id,
    'list' => $task->taskList()->value('name'),
    'progress' => $task->progress ?? null,
    'wbs' => $wbs?->code,
    'wbs_progress' => $wbs?->progress ?? null,
    'wbs_status' => $wbs?->status ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
