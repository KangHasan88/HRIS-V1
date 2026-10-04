<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$board = DB::table('boards')->where('id', 6)->first();
if (!$board) {
    throw new RuntimeException('HRIS board ID 6 not found.');
}

$now = now();
DB::table('task_lists')
    ->where('board_id', $board->id)
    ->update([
        'color' => '#e2e8f0',
        'updated_at' => $now,
    ]);

DB::table('task_lists')
    ->where('board_id', $board->id)
    ->where('name', 'Icebox')
    ->update([
        'name' => 'Failed',
        'updated_at' => $now,
    ]);

echo DB::table('task_lists')
    ->where('board_id', $board->id)
    ->orderBy('order')
    ->get(['name', 'color'])
    ->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
