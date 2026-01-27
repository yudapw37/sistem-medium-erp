<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::when($request->q, function ($query, $q) {
            $query->where('name', 'like', '%' . $q . '%');
        })
        ->latest()
        ->paginate(10);

        return Inertia::render('Dashboard/Warehouses/Index', [
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Warehouses/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        Warehouse::create([
            'name'        => $request->name,
            'location'    => $request->location,
            'description' => $request->description,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        return Inertia::render('Dashboard/Warehouses/Edit', [
            'warehouse' => $warehouse,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $warehouse->update([
            'name'        => $request->name,
            'location'    => $request->location,
            'description' => $request->description,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }

    /**
     * Sync all products to this warehouse.
     */
    public function syncProducts(Warehouse $warehouse)
    {
        $products = \App\Models\Product::all();
        $count = 0;

        foreach ($products as $product) {
            $exists = \App\Models\ProductWarehouse::where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->exists();

            if (!$exists) {
                \App\Models\ProductWarehouse::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'stock' => 0,
                ]);
                
                // Also create initial stock mutation record
                \App\Models\ProductStock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'type' => 'in',
                    'qty' => 0,
                    'previous_stock' => 0,
                    'current_stock' => 0,
                    'user_id' => auth()->id(),
                    'note' => 'System sync to warehouse: ' . $warehouse->name,
                ]);
                
                $count++;
            }
        }

        return redirect()->route('warehouses.index')->with('success', $count . ' produk berhasil disinkronkan ke ' . $warehouse->name);
    }
}
