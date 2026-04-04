<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $returns = PurchaseReturn::with(['supplier', 'warehouse'])
            ->when($request->q, function ($query, $q) {
                $query->where('code', 'like', '%' . $q . '%');
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/PurchaseReturns/Index', [
            'returns' => $returns,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/PurchaseReturns/Create', [
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'grand_total' => 'required|integer|min:0',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.buy_price'  => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate Code
            $code = 'PR-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $return = PurchaseReturn::create([
                'code'         => $code,
                'supplier_id'  => $request->supplier_id,
                'purchase_id'  => $request->purchase_id,
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
                    'buy_price'  => $item['buy_price'],
                ]);
            }
        });

        return redirect()->route('purchase-returns.index')->with('success', 'Return pembelian created as draft.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $return = PurchaseReturn::with(['supplier', 'warehouse', 'details.product', 'user'])->findOrFail($id);

        return Inertia::render('Dashboard/PurchaseReturns/Show', [
            'purchaseReturn' => $return,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $return = PurchaseReturn::with(['details.product'])->findOrFail($id);

        if ($return->status === 'finalized') {
            return redirect()->route('purchase-returns.index')->with('error', 'Cannot edit finalized return.');
        }

        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/PurchaseReturns/Edit', [
            'purchaseReturn' => $return,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $return = PurchaseReturn::findOrFail($id);

        if ($return->status === 'finalized') {
            return redirect()->route('purchase-returns.index')->with('error', 'Cannot edit finalized return.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'Purchase_id' => 'nullable|exists:purchases,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'grand_total' => 'required|integer|min:0',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.buy_price'  => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $return) {
            $return->update([
                'supplier_id'  => $request->supplier_id,
                'Purchase_id'  => $request->Purchase_id,
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
                    'buy_price'  => $item['buy_price'],
                ]);
            }
        });

        return redirect()->route('purchase-returns.index')->with('success', 'Return pembelian updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $return = PurchaseReturn::findOrFail($id);

        if ($return->status === 'finalized') {
            return redirect()->route('purchase-returns.index')->with('error', 'Cannot delete finalized return.');
        }

        $return->details()->delete();
        $return->delete();

        return redirect()->route('purchase-returns.index')->with('success', 'Return pembelian deleted successfully.');
    }

    /**
     * Finalize purchase return and decrease stock.
     */
    public function finalize($id)
    {
        $return = PurchaseReturn::with(['details', 'Purchase'])->findOrFail($id);

        if ($return->status === 'finalized') {
            return redirect()->route('purchase-returns.index')->with('error', 'Return already finalized.');
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
                
                // Decrease stock only (return to supplier)
                $productWarehouse->stock -= $detail->qty;
                $productWarehouse->save();

                // Log Stock Mutation
                ProductStock::create([
                    'product_id'          => $product->id,
                    'warehouse_id'        => $return->warehouse_id,
                    'type'                => 'out',
                    'qty'                 => $detail->qty,
                    'previous_stock'      => $previous_stock,
                    'current_stock'       => $productWarehouse->stock,
                    'transaction_id'      => null,
                    'user_id'             => auth()->id(),
                    'purchase_return_id'  => $return->id,
                    'note'                => 'Purchase Return ' . $return->code,
                ]);
            }

            $return->update([
                'status'       => 'finalized',
                'finalized_at' => now(),
            ]);

            // Create Journal and reduce Payable if linked to credit purchase
            if ($return->grand_total > 0) {
                \App\Services\JournalService::createPurchaseReturnJournal($return);

                if ($return->Purchase && in_array($return->Purchase->payment_type, ['tempo', 'credit'])) {
                    \App\Models\PayablePayment::create([
                        'reference'      => \App\Models\PayablePayment::generateReference(),
                        'purchase_id'    => $return->purchase_id,
                        'supplier_id'    => $return->supplier_id,
                        'payment_date'   => $return->date,
                        'amount'         => $return->grand_total,
                        'payment_method' => 'other',
                        'notes'          => 'Otomatis: Pengurangan hutang dari Retur Pembelian ' . $return->code,
                        'user_id'        => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('purchase-returns.index')->with('success', 'Return finalized and stock decreased successfully.');
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

    /**
     * Get purchases (PO) for a specific supplier.
     */
    public function getPurchasesBySupplier($supplierId)
    {
        $purchases = \App\Models\Purchase::where('supplier_id', $supplierId)
            ->where('status', 'finalized')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['id', 'invoice', 'grand_total', 'payment_type']);

        return response()->json($purchases);
    }
}
