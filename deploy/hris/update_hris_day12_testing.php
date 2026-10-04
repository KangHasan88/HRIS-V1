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
    ->where('title', 'like', '[DAY-12]%')
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
    'content' => 'Testing 90%: DAY-12 Flow karyawan ESS selesai. Implementasi mencakup halaman Alur ESS Saya di portal karyawan port 8092, ringkasan progress ESS, grouping Profil & Data Pribadi, Pengajuan Operasional, Klaim & Payroll, Dokumen & Bantuan, status Selesai/Berjalan/Perlu Aksi, shortcut ke halaman ESS terkait, form-help, sidebar employee-only, serta test untuk memastikan menu admin tidak tampil di ESS. URL: https://31.97.106.123:8092/ess/flow-center. Full test: 108 passed. Smoke HTTPS ESS: status 200.',
    'mentions' => [],
    'likes_count' => 0,
]);

echo json_encode([
    'code' => 'DAY-12',
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
