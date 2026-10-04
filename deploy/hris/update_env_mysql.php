<?php

$path = '/var/www/kurmigo-hris/.env';
$env = file_get_contents($path);

$updates = [
    'APP_NAME' => '"Kurmigo HRIS"',
    'APP_URL' => 'http://31.97.106.123:8091',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'kurmigo_hris',
    'DB_USERNAME' => 'kurmigo_hris',
    'DB_PASSWORD' => 'KurmigoHris@2026',
    'SESSION_DRIVER' => 'database',
    'CACHE_STORE' => 'database',
    'QUEUE_CONNECTION' => 'database',
];

foreach ($updates as $key => $value) {
    $line = $key . '=' . $value;
    if (preg_match('/^#?\s*' . preg_quote($key, '/') . '=.*/m', $env)) {
        $env = preg_replace('/^#?\s*' . preg_quote($key, '/') . '=.*/m', $line, $env);
        continue;
    }

    $env .= PHP_EOL . $line;
}

$backup = $path . '.bak-mysql-' . date('Ymd-His');
copy($path, $backup);
file_put_contents($path, $env);

echo "Updated {$path}\nBackup {$backup}\n";
