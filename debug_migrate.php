<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
} catch (\Exception $e) {
    file_put_contents('migration_error_raw.txt', $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine());
}
