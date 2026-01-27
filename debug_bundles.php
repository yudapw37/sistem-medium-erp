<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\ProductBundle;
use App\Models\Warehouse;
use App\Models\ProductWarehouse;

$out = "--- DEBUG BUNDLE STOCK ---\n";
$warehouses = Warehouse::all();
$bundles = ProductBundle::with('items.product')->get();

foreach ($bundles as $bundle) {
    $out .= "Bundle: {$bundle->name} ({$bundle->code})\n";
    $status = $bundle->is_active ? "ACTIVE" : "INACTIVE";
    $out .= "Status: {$status}\n";
    foreach ($warehouses as $wh) {
        $out .= "  Warehouse [ID:{$wh->id}]: {$wh->name}\n";
        $canMake = PHP_INT_MAX;
        foreach ($bundle->items as $item) {
            $pw = ProductWarehouse::where('product_id', $item->product_id)
                ->where('warehouse_id', $wh->id)
                ->first();
            $stock = $pw ? $pw->stock : 0;
            $possible = floor($stock / $item->qty);
            $canMake = min($canMake, $possible);
            $out .= "    - Component: " . ($item->product ? $item->product->title : 'MISSING_PRODUCT') . " | Need: {$item->qty} | Avail: {$stock} (Can make: {$possible})\n";
        }
        $finalStock = ($canMake === PHP_INT_MAX) ? 0 : $canMake;
        $out .= "  => TOTAL BUNDLE STOCK in this WH: {$finalStock}\n";
    }
    $out .= "--------------------------\n";
}

file_put_contents('debug_log.txt', $out);
echo "Debug log written to debug_log.txt\n";
