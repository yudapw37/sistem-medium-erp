<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\SaleDetail;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class MinimumStockReportController extends Controller
{
    public function index(Request $request)
    {
        // Get parameters with defaults
        $referenceDate = $request->reference_date ?? Carbon::today()->format('Y-m-d');
        $periodMonths = $request->period_months ?? 2;
        $safetyDays = $request->safety_days ?? 7;
        $warehouseId = $request->warehouse_id ?? null;

        // Calculate date range
        $endDate = Carbon::parse($referenceDate)->endOfDay();
        $startDate = Carbon::parse($referenceDate)->subMonths($periodMonths)->startOfDay();
        $totalDays = $startDate->diffInDays($endDate);

        // Get average daily sales per product
        $avgSalesQuery = SaleDetail::select('product_id', DB::raw("SUM(qty) as total_sold"))
            ->whereNotNull('product_id')
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'finalized')
                  ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('product_id');

        $avgSales = $avgSalesQuery->get()->keyBy('product_id');

        // Get current stock per product (optionally filtered by warehouse)
        $stockQuery = ProductWarehouse::select('product_id', DB::raw('SUM(stock) as current_stock'))
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->groupBy('product_id');

        $currentStocks = $stockQuery->get()->keyBy('product_id');

        // Build report data
        $products = Product::with('category')
            ->where('is_sellable', true)
            ->orderBy('title')
            ->get()
            ->map(function ($product) use ($avgSales, $currentStocks, $totalDays, $safetyDays) {
                $totalSold = $avgSales->get($product->id)?->total_sold ?? 0;
                $avgDailySales = $totalDays > 0 ? round($totalSold / $totalDays, 2) : 0;
                $minStock = ceil($avgDailySales * $safetyDays);
                $currentStock = $currentStocks->get($product->id)?->current_stock ?? 0;

                return [
                    'id' => $product->id,
                    'barcode' => $product->barcode,
                    'title' => $product->title,
                    'category' => $product->category?->name,
                    'total_sold' => $totalSold,
                    'avg_daily_sales' => $avgDailySales,
                    'min_stock' => $minStock,
                    'current_stock' => (int) $currentStock,
                    'status' => $currentStock <= $minStock ? 'critical' : 'safe',
                    'diff' => $currentStock - $minStock,
                ];
            });

        // Filter only critical if requested
        if ($request->critical_only) {
            $products = $products->filter(fn($p) => $p['status'] === 'critical');
        }

        // Sort by status (critical first) then by diff
        $products = $products->sortBy([
            ['status', 'desc'],
            ['diff', 'asc'],
        ])->values();

        $warehouses = Warehouse::orderBy('name')->get();

        // Calculate stats before pagination
        $allProductsCount = $products->count();
        $criticalCount = $products->where('status', 'critical')->count();
        $safeCount = $products->where('status', 'safe')->count();

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

        return Inertia::render('Dashboard/Reports/MinimumStock/Index', [
            'products' => $paginatedProducts,
            'warehouses' => $warehouses,
            'filters' => [
                'reference_date' => $referenceDate,
                'period_months' => (int) $periodMonths,
                'safety_days' => (int) $safetyDays,
                'warehouse_id' => $warehouseId,
                'critical_only' => (bool) $request->critical_only,
            ],
            'stats' => [
                'total_products' => $allProductsCount,
                'critical_count' => $criticalCount,
                'safe_count' => $safeCount,
                'period_days' => $totalDays,
            ],
        ]);
    }
}
