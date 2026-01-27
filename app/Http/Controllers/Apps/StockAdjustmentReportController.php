<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockAdjustmentReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request)
    {
        // Query filters
        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;
        $warehouseId = $request->warehouse_id ?? null;
        $type = $request->type ?? null;
        $search = $request->search ?? null;

        $adjustments = StockAdjustment::with(['warehouse', 'user', 'details.product'])
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/Reports/StockAdjustment/Index', [
            'adjustments' => $adjustments,
            'warehouses' => $warehouses,
            'filters' => [
                'start_date'   => $startDate,
                'end_date'     => $endDate,
                'warehouse_id' => $warehouseId,
                'type'         => $type,
                'search'       => $search,
            ]
        ]);
    }
}
