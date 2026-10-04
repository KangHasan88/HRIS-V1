<?php

use App\Models\Board;
use App\Models\Task;
use App\Models\TaskActivity;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$board = Board::query()->findOrFail(6);
$testing = $board->lists()->where('name', 'Testing')->firstOrFail();
$task = Task::with('checklists.items')->findOrFail(253);
$task->task_list_id = $testing->id;
$task->progress = 90;
$task->save();

foreach ($task->checklists as $checklist) {
    foreach ($checklist->items as $item) {
        $item->is_checked = true;
        $item->save();
    }
}

TaskActivity::query()->create([
    'task_id' => $task->id,
    'user_id' => $board->user_id,
    'action' => 'commented',
    'old_value' => null,
    'new_value' => 'Testing 90%: MANAGE-052 selesai. Inbox approval manager, bulk approve/reject, indikator risiko, delegasi approval, audit trail, route/menu, test 90 passed, smoke HTTPS 200 OK.',
]);

echo json_encode([
    'task' => $task->title,
    'list' => 'Testing',
    'progress' => $task->progress,
    'checked' => $task->checklists->flatMap->items->where('is_checked', true)->count(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
