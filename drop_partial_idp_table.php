<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Illuminate\Support\Facades\Schema::dropIfExists('individual_development_plans');

echo "dropped individual_development_plans if existed".PHP_EOL;
