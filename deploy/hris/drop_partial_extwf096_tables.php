<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Schema::dropIfExists('external_workforce_contracts');
Schema::dropIfExists('external_workforce_vendors');

echo "partial external workforce tables dropped\n";
