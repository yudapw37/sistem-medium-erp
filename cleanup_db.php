<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    echo "Dropping bundle_id from carts...\n";
    Schema::table('carts', function ($table) {
        $table->dropForeign(['bundle_id']);
        $table->dropColumn('bundle_id');
    });
} catch (\Exception $e) {
    echo "Carts: " . $e->getMessage() . "\n";
    try {
        DB::statement('ALTER TABLE carts DROP COLUMN bundle_id');
        echo "Dropped via raw SQL\n";
    } catch (\Exception $e2) {
        echo "Carts Raw: " . $e2->getMessage() . "\n";
    }
}

try {
    echo "Dropping bundle_id from transaction_details...\n";
    Schema::table('transaction_details', function ($table) {
        $table->dropForeign(['bundle_id']);
        $table->dropColumn('bundle_id');
    });
} catch (\Exception $e) {
    echo "Transaction Details: " . $e->getMessage() . "\n";
    try {
        DB::statement('ALTER TABLE transaction_details DROP COLUMN bundle_id');
        echo "Dropped via raw SQL\n";
    } catch (\Exception $e2) {
        echo "Transaction Details Raw: " . $e2->getMessage() . "\n";
    }
}
echo "Cleanup done.\n";
