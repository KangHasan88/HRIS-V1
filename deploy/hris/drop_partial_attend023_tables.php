<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Schema::dropIfExists('attendance_location_policies');
Schema::dropIfExists('attendance_geofences');

echo "partial ATTEND-023 tables dropped\n";
