<?php

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\WbsItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boardId = 6;

$task = Task::whereHas('taskList', fn ($query) => $query->where('board_id', $boardId))
    ->where('title', 'like', '[PERFOR-075]%')
    ->firstOrFail();

$testingList = TaskList::where('board_id', $boardId)->where('name', 'Testing')->firstOrFail();

$task->task_list_id = $testingList->id;
if (Schema::hasColumn('tasks', 'progress')) {
    $task->progress = 90;
}
$task->save();

$checklistIds = Checklist::where('task_id', $task->id)->pluck('id');
ChecklistItem::whereIn('checklist_id', $checklistIds)->update(['is_checked' => true]);

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

$actor = User::where('email', 'codex')->orWhere('name', 'codex')->first() ?: User::first();

Comment::create([
    'task_id' => $task->id,
    'user_id' => $actor?->id,
    'content' => 'Testing 90%: PERFOR-075 Eksplorasi career path dan marketplace mobilitas internal selesai. Implementasi mencakup migration internal_mobility_opportunities dan internal_mobility_applications, model relasi opportunity-position-company-branch dan application-employee-succession-IDP, halaman /performance/internal-mobility, menu Career Marketplace di sidebar, form buka opportunity internal, form screening mobilitas, movement type promotion/rotation/lateral/project, readiness requirement, eligibility, development benefit, match score, manager support, mobility constraint, target movement date, pipeline table, audit log, feature test, migrate production, dan smoke HTTPS. URL: https://31.97.106.123:8091/performance/internal-mobility. Full test: 123 passed. Smoke HTTPS: status 200.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'PERFOR-075',
    'task_id' => $task->id,
    'title' => Str::limit($task->title, 100),
    'list' => $testingList->name,
    'progress' => $task->progress ?? null,
    'checked' => ChecklistItem::whereIn('checklist_id', $checklistIds)->where('is_checked', true)->count(),
    'total_checklist' => ChecklistItem::whereIn('checklist_id', $checklistIds)->count(),
    'wbs' => $wbs?->code,
    'wbs_status' => $wbs?->status ?? null,
    'wbs_progress' => $wbs?->progress ?? null,
], JSON_PRETTY_PRINT).PHP_EOL;
