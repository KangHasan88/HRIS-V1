<?php

use Illuminate\Support\Facades\Schema;

require '/var/www/kurmigo-hris/vendor/autoload.php';

$app = require '/var/www/kurmigo-hris/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Schema::dropIfExists('recruitment_employee_conversions');

echo "dropped recruitment_employee_conversions if exists\n";
