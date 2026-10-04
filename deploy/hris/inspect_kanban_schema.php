<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "tasks\n";
echo json_encode(Schema::getColumnListing('tasks'), JSON_PRETTY_PRINT) . PHP_EOL;
echo "wbs_items\n";
echo json_encode(Schema::getColumnListing('wbs_items'), JSON_PRETTY_PRINT) . PHP_EOL;
echo "checklists\n";
echo json_encode(Schema::getColumnListing('checklists'), JSON_PRETTY_PRINT) . PHP_EOL;
echo "checklist_items\n";
echo json_encode(Schema::getColumnListing('checklist_items'), JSON_PRETTY_PRINT) . PHP_EOL;
echo "labels\n";
echo json_encode(Schema::getColumnListing('labels'), JSON_PRETTY_PRINT) . PHP_EOL;
echo "task_label\n";
echo json_encode(Schema::getColumnListing('task_label'), JSON_PRETTY_PRINT) . PHP_EOL;
