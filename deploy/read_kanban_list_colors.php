<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$boards = DB::table('boards')->select('id', 'name')->orderBy('id')->get();
$result = [];
foreach ($boards as $board) {
    $result[] = [
        'board' => "{$board->id}: {$board->name}",
        'lists' => DB::table('task_lists')
            ->where('board_id', $board->id)
            ->orderBy('order')
            ->get(['name', 'color'])
            ->map(fn ($list) => "{$list->name}={$list->color}")
            ->all(),
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
