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

class SlowMovingReportController extends Controller
{
    public function index(Request $request)
    {
        // Get parameters with defaults
        $referenceDate = $request->reference_date ?? Carbon::today()->format('Y-m-d');
        $periodMonths = $request->period_months ?? 3;
        $warehouseId = $request->warehouse_id ?? null;
        $threshold = $request->threshold ?? 5; // Products with <= 5 sales are considered slow

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
            ->get()
            ->keyBy('product_id');

        // Get all sellable products
        $products = Product::with('category')
            ->where('is_sellable', true)
            ->orderBy('title')
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
            });

        // Filter only slow moving products (below or equal threshold)
        $products = $products->filter(fn($p) => $p['total_sold'] <= $threshold);

        // Sort by total sold ascending (least sold first)
        $products = $products->sortBy('total_sold')->values();

        $warehouses = Warehouse::orderBy('name')->get();

        // Calculate stats before pagination
        $allProductsCount = $products->count();
        $zeroSalesCount = $products->where('total_sold', 0)->count();

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

        return Inertia::render('Dashboard/Reports/SlowMoving/Index', [
            'products' => $paginatedProducts,
            'warehouses' => $warehouses,
            'filters' => [
                'reference_date' => $referenceDate,
                'period_months' => (int) $periodMonths,
                'warehouse_id' => $warehouseId,
                'threshold' => (int) $threshold,
            ],
            'stats' => [
                'total_slow' => $allProductsCount,
                'zero_sales' => $zeroSalesCount,
                'period_days' => $totalDays,
            ],
        ]);
    }
}
