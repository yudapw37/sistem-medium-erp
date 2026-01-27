<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class FastMovingReportController extends Controller
{
    public function index(Request $request)
    {
        // Get parameters with defaults
        $referenceDate = $request->reference_date ?? Carbon::today()->format('Y-m-d');
        $periodMonths = $request->period_months ?? 1;
        $warehouseId = $request->warehouse_id ?? null;

        // Calculate date range
        $endDate = Carbon::parse($referenceDate)->endOfDay();
        $startDate = Carbon::parse($referenceDate)->subMonths($periodMonths)->startOfDay();
        $totalDays = $startDate->diffInDays($endDate);

        // Get total sales per product
        $salesQuery = SaleDetail::select('product_id', DB::raw("SUM(qty) as total_sold"))
            ->whereNotNull('product_id')
            ->whereHas('sale', function ($q) use ($startDate, $endDate, $warehouseId) {
                $q->where('status', 'finalized')
                  ->whereBetween('created_at', [$startDate, $endDate]);
                
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->get()
            ->keyBy('product_id');

        // Get products with sales data
        $productIds = $salesQuery->keys()->toArray();
        
        $products = Product::with('category')
            ->whereIn('id', $productIds)
            ->get()
            ->map(function ($product) use ($salesQuery, $totalDays) {
                $totalSold = $salesQuery->get($product->id)?->total_sold ?? 0;
                $avgDailySales = $totalDays > 0 ? round($totalSold / $totalDays, 2) : 0;

                return [
                    'id' => $product->id,
                    'barcode' => $product->barcode,
                    'title' => $product->title,
                    'category' => $product->category?->name,
                    'total_sold' => (int) $totalSold,
                    'avg_daily_sales' => $avgDailySales,
                ];
            })
            ->sortByDesc('total_sold')
            ->values();

        // Add ranking
        $products = $products->map(function ($product, $index) {
            $product['rank'] = $index + 1;
            return $product;
        });

        $warehouses = Warehouse::orderBy('name')->get();

        // Calculate stats before pagination
        $allProductsCount = $products->count();
        $totalSoldAll = $products->sum('total_sold');

        // Manual pagination
        $page = $request->page ?? 1;
        $perPage = 15;
        $paginatedProducts = new LengthAwarePaginator(
            $products->forPage($page, $perPage)->values(),
            $allProductsCount,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Dashboard/Reports/FastMoving/Index', [
            'products' => $paginatedProducts,
            'warehouses' => $warehouses,
            'filters' => [
                'reference_date' => $referenceDate,
                'period_months' => (int) $periodMonths,
                'warehouse_id' => $warehouseId,
            ],
            'stats' => [
                'total_products' => $allProductsCount,
                'total_sold' => $totalSoldAll,
                'period_days' => $totalDays,
            ],
        ]);
    }
}
