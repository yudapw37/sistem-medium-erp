<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportStockController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request)
    {
        //query start date
        $startDate = $request->start_date ?? null;
        //query end date
        $endDate = $request->end_date ?? null;
        //query product
        $product = $request->product ?? null;
        //query warehouse
        $warehouseId = $request->warehouse_id ?? null;

        $stocks = ProductStock::with(['product', 'transaction', 'user', 'warehouse'])
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->when($product, function ($query) use ($product) {
                $query->whereHas('product', function ($q) use ($product) {
                    $q->where('title', 'like', '%' . $product . '%');
                });
            })
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $warehouses = Warehouse::all();

        return Inertia::render('Dashboard/Reports/Stock/Index', [
            'stocks' => $stocks,
            'warehouses' => $warehouses,
            'filters' => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'product'    => $product,
                'warehouse_id' => $warehouseId,
            ]
        ]);
    }
}
