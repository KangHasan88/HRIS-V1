<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Schema::dropIfExists('payroll_disbursement_approvals');
Schema::dropIfExists('payroll_disbursement_items');
Schema::dropIfExists('payroll_disbursement_batches');

echo "partial PAYROL-039 tables dropped\n";
