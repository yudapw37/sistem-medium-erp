<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = ['carts', 'transaction_details', 'product_bundles', 'sale_details'];
$out = "";

foreach ($tables as $table) {
    $out .= "--- Table: $table ---\n";
    try {
        if (!Schema::hasTable($table)) {
            $out .= "Table does not exist.\n";
            continue;
        }
        $columns = Schema::getColumnListing($table);
        foreach ($columns as $column) {
            $type = Schema::getColumnType($table, $column);
            $out .= "- $column ($type)\n";
        }
        
        // Check for foreign keys if on MySQL
        if (DB::getDriverName() === 'mysql') {
            $fks = DB::select("
                SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table]);
            
            foreach ($fks as $fk) {
                $out .= "  FK: {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME} ({$fk->CONSTRAINT_NAME})\n";
            }
        }
    } catch (\Exception $e) {
        $out .= "Error: " . $e->getMessage() . "\n";
    }
}

file_put_contents('db_info.txt', $out);
echo "Inspection complete. Output written to db_info.txt\n";
