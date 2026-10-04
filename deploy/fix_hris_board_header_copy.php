<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$now = now();
$boardId = 6;

$description = implode("\n", [
    'Backlog pengembangan Kurmigo HRIS untuk membangun platform HR, Payroll, ESS, dan Mobile ESS berbasis modul yang bisa diaktifkan/nonaktifkan per perusahaan.',
    '',
    'Scope utama: Core HR, attendance, leave, payroll, PPh 21/Coretax, recruitment, assessment integration, performance, learning, analytics, integration, compliance, security, dan mobile WebView.',
    '',
    'Target delivery: 10 Jul 2026 - 09 Aug 2026. Semua progress coding wajib diupdate melalui status Kanban, checklist card, dan komentar blocker.',
]);

$updated = DB::table('boards')
    ->where('id', $boardId)
    ->update([
        'name' => 'HRIS',
        'description' => $description,
        'updated_at' => $now,
    ]);

$board = DB::table('boards')->where('id', $boardId)->first();
$activeTasks = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->count();

$brandMentions = DB::table('tasks')
    ->join('task_lists', 'task_lists.id', '=', 'tasks.task_list_id')
    ->leftJoin('checklists', 'checklists.task_id', '=', 'tasks.id')
    ->leftJoin('checklist_items', 'checklist_items.checklist_id', '=', 'checklists.id')
    ->where('task_lists.board_id', $boardId)
    ->whereNull('tasks.archived_at')
    ->where(function ($query) {
        $query->where('tasks.title', 'like', '%Talenta%')
            ->orWhere('tasks.description', 'like', '%Talenta%')
            ->orWhere('checklist_items.name', 'like', '%Talenta%');
    })
    ->count();

echo json_encode([
    'board_id' => $boardId,
    'updated' => $updated,
    'name' => $board->name,
    'description' => $board->description,
    'active_tasks' => $activeTasks,
    'brand_mentions' => $brandMentions,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
