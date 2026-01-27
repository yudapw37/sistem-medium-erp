<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\SaleReturn;
use App\Models\Customer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SaleReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $returns = SaleReturn::with(['Customer', 'warehouse'])
            ->when($request->q, function ($query, $q) {
                $query->where('code', 'like', '%' . $q . '%');
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/SaleReturns/Index', [
            'returns' => $returns,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $Customers = Customer::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/SaleReturns/Create', [
            'Customers' => $Customers,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'Customer_id' => 'required|exists:Customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'grand_total' => 'required|integer|min:0',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.sell_price'  => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate Code
            $code = 'PR-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $return = SaleReturn::create([
                'code'         => $code,
                'Customer_id'  => $request->Customer_id,
                'warehouse_id' => $request->warehouse_id,
                'user_id'      => auth()->id(),
                'date'         => $request->date,
                'grand_total'  => $request->grand_total,
                'notes'        => $request->notes,
                'status'       => 'draft',
            ]);

            foreach ($request->items as $item) {
                $return->details()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'sell_price'  => $item['sell_price'],
                ]);
            }
        });

        return redirect()->route('sale-returns.index')->with('success', 'Return Penjualan created as draft.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $return = SaleReturn::with(['Customer', 'warehouse', 'details.product', 'user'])->findOrFail($id);

        return Inertia::render('Dashboard/SaleReturns/Show', [
            'saleReturn' => $return,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $return = SaleReturn::with(['details.product'])->findOrFail($id);

        if ($return->status === 'finalized') {
            return redirect()->route('sale-returns.index')->with('error', 'Cannot edit finalized return.');
        }

        $Customers = Customer::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/SaleReturns/Edit', [
            'saleReturn' => $return,
            'Customers' => $Customers,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $return = SaleReturn::findOrFail($id);

        if ($return->status === 'finalized') {
            return redirect()->route('sale-returns.index')->with('error', 'Cannot edit finalized return.');
        }

        $request->validate([
            'Customer_id' => 'required|exists:Customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'grand_total' => 'required|integer|min:0',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.sell_price'  => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $return) {
            $return->update([
                'Customer_id'  => $request->Customer_id,
                'warehouse_id' => $request->warehouse_id,
                'date'         => $request->date,
                'grand_total'  => $request->grand_total,
                'notes'        => $request->notes,
            ]);

            // Delete old details
            $return->details()->delete();

            // Create new details
            foreach ($request->items as $item) {
                $return->details()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'sell_price'  => $item['sell_price'],
                ]);
            }
        });

        return redirect()->route('sale-returns.index')->with('success', 'Return Penjualan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $return = SaleReturn::findOrFail($id);

        if ($return->status === 'finalized') {
            return redirect()->route('sale-returns.index')->with('error', 'Cannot delete finalized return.');
        }

        $return->details()->delete();
        $return->delete();

        return redirect()->route('sale-returns.index')->with('success', 'Return Penjualan deleted successfully.');
    }

    /**
     * Finalize sale return and increase stock.
     */
    public function finalize($id)
    {
        $return = SaleReturn::with('details')->findOrFail($id);

        if ($return->status === 'finalized') {
            return redirect()->route('sale-returns.index')->with('error', 'Return already finalized.');
        }

        DB::transaction(function () use ($return) {
            foreach ($return->details as $detail) {
                $product = Product::find($detail->product_id);

                // Get or Create ProductWarehouse
                $productWarehouse = ProductWarehouse::firstOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $return->warehouse_id],
                    ['stock' => 0, 'stock_fix' => 0]
                );

                $previous_stock = $productWarehouse->stock;
                
                // Increase stock only (return from customer)
                $productWarehouse->stock += $detail->qty;
                $productWarehouse->save();

                // Log Stock Mutation
                ProductStock::create([
                    'product_id'          => $product->id,
                    'warehouse_id'        => $return->warehouse_id,
                    'type'                => 'in',
                    'qty'                 => $detail->qty,
                    'previous_stock'      => $previous_stock,
                    'current_stock'       => $productWarehouse->stock,
                    'transaction_id'      => null,
                    'user_id'             => auth()->id(),
                    'sale_return_id'      => $return->id,
                    'note'                => 'Sale Return ' . $return->code,
                ]);
            }

            $return->update([
                'status'       => 'finalized',
                'finalized_at' => now(),
            ]);
        });

        return redirect()->route('sale-returns.index')->with('success', 'Return finalized and stock increased successfully.');
    }
    
    /**
     * Search product for return input
     */
    public function searchProduct(Request $request)
    {
        $products = Product::where('title', 'like', '%' . $request->q . '%')
            ->orWhere('barcode', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get();
            
        return response()->json($products);
    }
}
