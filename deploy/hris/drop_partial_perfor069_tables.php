<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Schema::dropIfExists('performance_review_responses');
Schema::dropIfExists('performance_review_forms');
Schema::dropIfExists('performance_review_cycles');

echo "partial dropped\n";
