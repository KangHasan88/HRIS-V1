<?php

use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['tasks', 'task_lists', 'wbs_items', 'gantt_settings', 'checklists', 'checklist_items', 'labels', 'task_label'] as $table) {
    echo $table.': '.json_encode(Schema::getColumnListing($table)).PHP_EOL;
}
