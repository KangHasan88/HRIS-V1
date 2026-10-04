<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Str;

$boardId = 6;
$now = now();
$taskListIds = DB::table('task_lists')->where('board_id', $boardId)->pluck('id');

$linkedTaskIds = DB::table('wbs_items')
    ->where('board_id', $boardId)
    ->whereNotNull('task_id')
    ->pluck('task_id');

$tasks = DB::table('tasks')
    ->whereIn('task_list_id', $taskListIds)
    ->whereNull('archived_at')
    ->whereNotIn('id', $linkedTaskIds)
    ->orderBy('start_date')
    ->orderBy('order')
    ->orderBy('id')
    ->get();

$existingCodes = DB::table('wbs_items')->where('board_id', $boardId)->pluck('code')->filter()->all();
$numericCodes = array_values(array_filter(array_map(function ($code) {
    return ctype_digit((string) $code) ? (int) $code : null;
}, $existingCodes), fn ($value) => $value !== null));
$nextCode = $numericCodes ? max($numericCodes) + 1 : 1;
$maxOrder = (int) DB::table('wbs_items')->where('board_id', $boardId)->max('sort_order');

$created = [];
foreach ($tasks as $task) {
    while (in_array((string) $nextCode, array_map('strval', $existingCodes), true)) {
        $nextCode++;
    }

    $maxOrder++;
    DB::table('wbs_items')->insert([
        'board_id' => $boardId,
        'parent_id' => null,
        'task_id' => $task->id,
        'code' => (string) $nextCode,
        'title' => $task->title,
        'target' => Str::limit(strip_tags((string) $task->description), 220),
        'notes' => null,
        'sort_order' => $maxOrder,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $created[] = $task->title;
    $existingCodes[] = (string) $nextCode;
    $nextCode++;
}

echo json_encode([
    'board_id' => $boardId,
    'created' => count($created),
    'created_titles' => $created,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
