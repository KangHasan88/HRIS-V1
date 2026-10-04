<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo DB::table('boards')
    ->select('id', 'name')
    ->orderByDesc('id')
    ->limit(20)
    ->get()
    ->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
