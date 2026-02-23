<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = Illuminate\Support\Facades\Schema::getColumnListing('old_order');
echo "old_order columns: " . implode(', ', $cols) . "\n";

$cols2 = Illuminate\Support\Facades\Schema::getColumnListing('old_orderdetail');
echo "old_orderdetail columns: " . implode(', ', $cols2) . "\n";

// Check sample data
$sample = Illuminate\Support\Facades\DB::table('old_order')->limit(3)->get();
foreach ($sample as $row) {
    echo json_encode($row) . "\n";
}
