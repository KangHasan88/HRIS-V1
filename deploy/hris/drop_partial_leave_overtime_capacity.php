<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Schema::dropIfExists('leave_overtime_capacity_predictions');

echo json_encode([
    'table_exists' => Schema::hasTable('leave_overtime_capacity_predictions'),
], JSON_PRETTY_PRINT) . PHP_EOL;
