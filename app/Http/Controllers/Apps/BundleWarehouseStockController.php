<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ProductBundle;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class BundleWarehouseStockController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();

        // Build query for active bundles
        $query = ProductBundle::with(['items.product.warehouses'])
            ->where('is_active', true);

        // Filter by search (name or code)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        // Get bundles with pagination
        $bundles = $query->paginate(10)->withQueryString();

        // Transform bundles to calculate stock per warehouse
        $bundles->getCollection()->transform(function ($bundle) use ($warehouses) {
            $warehouseStocks = [];
            
            foreach ($warehouses as $warehouse) {
                $minStockAcrossComponents = null;

                foreach ($bundle->items as $item) {
                    $product = $item->product;
                    if (!$product) {
                        $minStockAcrossComponents = 0;
                        break;
                    }

                    // Get product stock in this warehouse
                    $warehouseData = $product->warehouses->firstWhere('id', $warehouse->id);
                    $stock = $warehouseData ? $warehouseData->pivot->stock : 0;

                    // How many bundles can we make with this component?
                    $possibleBundles = floor($stock / $item->qty);

                    if ($minStockAcrossComponents === null || $possibleBundles < $minStockAcrossComponents) {
                        $minStockAcrossComponents = $possibleBundles;
                    }
                }

                $warehouseStocks[$warehouse->id] = $minStockAcrossComponents ?? 0;
            }

            return [
                'id' => $bundle->id,
                'code' => $bundle->code,
                'name' => $bundle->name,
                'warehouse_stocks' => $warehouseStocks,
                'total_stock' => array_sum($warehouseStocks),
            ];
        });

        return Inertia::render('Dashboard/Reports/BundleStock/Index', [
            'bundles' => $bundles,
            'warehouses' => $warehouses,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }
}
