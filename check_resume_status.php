<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Cek kolom di tabel old_order
$columns = \DB::select("SHOW COLUMNS FROM old_order");
echo "Kolom di tabel old_order:\n";
echo str_pad("Field", 25) . str_pad("Type", 20) . str_pad("Null", 6) . str_pad("Default", 20) . "\n";
echo str_repeat("-", 75) . "\n";
foreach ($columns as $col) {
    echo str_pad($col->Field, 25) . str_pad($col->Type, 20) . str_pad($col->Null, 6) . str_pad($col->Default ?? 'NULL', 20) . "\n";
}
