<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseStockController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        // Build query
        $query = Product::with(['category:id,name', 'warehouses'])
            ->select('id', 'barcode', 'title', 'category_id');

        // Filter by search (product name or barcode)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Get products with pagination
        $products = $query->orderBy('title')->paginate(10)->withQueryString();

        // Transform products to include stock per warehouse
        $products->getCollection()->transform(function ($product) use ($warehouses) {
            $warehouseStocks = [];
            $totalStock = 0;
            $totalStockFix = 0;

            foreach ($warehouses as $warehouse) {
                // Find pivot data directly from product's warehouses relationship
                $warehouseData = $product->warehouses->firstWhere('id', $warehouse->id);
                
                $stockQty = $warehouseData ? $warehouseData->pivot->stock : 0;
                $stockFixQty = $warehouseData ? $warehouseData->pivot->stock_fix : 0;
                
                $warehouseStocks[$warehouse->id] = [
                    'stock' => $stockQty,
                    'stock_fix' => $stockFixQty,
                ];
                
                $totalStock += $stockQty;
                $totalStockFix += $stockFixQty;
            }

            return [
                'id' => $product->id,
                'barcode' => $product->barcode,
                'title' => $product->title,
                'category' => $product->category,
                'warehouse_stocks' => $warehouseStocks,
                'total_stock' => $totalStock,
                'total_stock_fix' => $totalStockFix,
            ];
        });

        return Inertia::render('Dashboard/Reports/WarehouseStock/Index', [
            'products' => $products,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'filters' => [
                'search' => $request->search,
                'category_id' => $request->category_id,
            ],
        ]);
    }
}
