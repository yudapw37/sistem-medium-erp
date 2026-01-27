<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $adjustments = StockAdjustment::with(['warehouse', 'user'])
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->warehouse_id, function ($query, $warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('code', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/StockInOut/Index', [
            'adjustments' => $adjustments,
            'warehouses' => $warehouses,
            'filters' => [
                'type' => $request->type,
                'warehouse_id' => $request->warehouse_id,
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/StockInOut/Create', [
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // Generate code
            $code = 'ADJ-' . Str::upper(Str::random(10));

            $adjustment = StockAdjustment::create([
                'code' => $code,
                'type' => $request->type,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
                'status' => 'draft', // Save as draft
            ]);

            foreach ($request->items as $item) {
                // Create detail only
                $adjustment->details()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);
            }
        });

        return redirect()->route('stock-adjustments.index')->with('success', 'Stock adjustment created as draft. Please finalize to update stock.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $adjustment = StockAdjustment::with(['warehouse', 'user', 'details.product'])->findOrFail($id);

        return Inertia::render('Dashboard/StockInOut/Show', [
            'adjustment' => $adjustment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $adjustment = StockAdjustment::with(['details.product'])->findOrFail($id);

        if ($adjustment->status === 'finalized') {
            return redirect()->route('stock-adjustments.index')->with('error', 'Cannot edit finalized adjustment.');
        }

        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/StockInOut/Edit', [
            'adjustment' => $adjustment,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $adjustment = StockAdjustment::with('details')->findOrFail($id);

        if ($adjustment->status === 'finalized') {
            return redirect()->route('stock-adjustments.index')->with('error', 'Cannot edit finalized adjustment.');
        }

        $request->validate([
            'type' => 'required|in:in,out',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $adjustment) {
            // Update adjustment
            $adjustment->update([
                'type' => $request->type,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);

            // Delete old details
            $adjustment->details()->delete();

            // Create new details (stock will be updated on finalize)
            foreach ($request->items as $item) {
                $adjustment->details()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);
            }
        });

        return redirect()->route('stock-adjustments.index')->with('success', 'Stock adjustment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $adjustment = StockAdjustment::with('details')->findOrFail($id);

        if ($adjustment->status === 'finalized') {
            return redirect()->route('stock-adjustments.index')->with('error', 'Cannot delete finalized adjustment.');
        }

        DB::transaction(function () use ($adjustment) {
            $adjustment->details()->delete();
            $adjustment->delete();
        });

        return redirect()->route('stock-adjustments.index')->with('success', 'Stock adjustment deleted successfully.');
    }

    /**
     * Finalize adjustment and update stock
     */
    public function finalize($id)
    {
        $adjustment = StockAdjustment::with(['details.product'])->findOrFail($id);

        if ($adjustment->status === 'finalized') {
            return redirect()->route('stock-adjustments.index')->with('error', 'Adjustment already finalized.');
        }

        DB::transaction(function () use ($adjustment) {
            $totalValue = 0;

            foreach ($adjustment->details as $detail) {
                // Get or Create ProductWarehouse
                $productWarehouse = ProductWarehouse::firstOrCreate(
                    ['product_id' => $detail->product_id, 'warehouse_id' => $adjustment->warehouse_id],
                    ['stock' => 0, 'stock_fix' => 0]
                );

                // Calculate value using buy_price
                $itemValue = $detail->product->buy_price * $detail->qty;
                $totalValue += $itemValue;

                // Update stock_fix
                if ($adjustment->type === 'in') {
                    $productWarehouse->stock_fix += $detail->qty;
                } else {
                    $productWarehouse->stock_fix -= $detail->qty;
                }

                $productWarehouse->save();
            }

            // Create accounting journal if value > 0
            if ($totalValue > 0) {
                $this->createJournal($adjustment, $totalValue);
            }

            $adjustment->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);
        });

        return redirect()->route('stock-adjustments.index')->with('success', 'Stock adjustment berhasil difinalisasi dengan jurnal akuntansi.');
    }

    /**
     * Create accounting journal for stock adjustment
     */
    private function createJournal($adjustment, $totalValue)
    {
        // Get account settings
        $inventoryAccountId = \App\Models\AccountSetting::getAccountId('inventory');
        $stockAdjustmentInAccountId = \App\Models\AccountSetting::getAccountId('stock_adjustment_in');
        $stockAdjustmentOutAccountId = \App\Models\AccountSetting::getAccountId('stock_adjustment_out');

        // Fallback to equity/expense if not set
        if (!$stockAdjustmentInAccountId) {
            $stockAdjustmentInAccountId = \App\Models\AccountSetting::getAccountId('equity');
        }
        if (!$stockAdjustmentOutAccountId) {
            $stockAdjustmentOutAccountId = \App\Models\AccountSetting::getAccountId('cogs');
        }

        // Create journal header
        $journal = \App\Models\Journal::create([
            'date' => $adjustment->date,
            'reference' => \App\Models\Journal::generateReference('SADJ'),
            'description' => 'Penyesuaian Stok ' . $adjustment->code . ' (' . ($adjustment->type === 'in' ? 'Masuk' : 'Keluar') . ')',
            'source_type' => StockAdjustment::class,
            'source_id' => $adjustment->id,
            'user_id' => auth()->id(),
        ]);

        // Create journal entries
        if ($adjustment->type === 'in') {
            // Stok Masuk: Debit Inventory, Credit Equity/Modal
            if ($inventoryAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $inventoryAccountId,
                    'debit' => $totalValue,
                    'credit' => 0,
                    'description' => 'Penambahan persediaan dari penyesuaian stok',
                ]);
            }
            if ($stockAdjustmentInAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $stockAdjustmentInAccountId,
                    'debit' => 0,
                    'credit' => $totalValue,
                    'description' => 'Modal/ekuitas dari penyesuaian stok masuk',
                ]);
            }
        } else {
            // Stok Keluar: Debit Expense, Credit Inventory
            if ($stockAdjustmentOutAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $stockAdjustmentOutAccountId,
                    'debit' => $totalValue,
                    'credit' => 0,
                    'description' => 'Beban selisih persediaan',
                ]);
            }
            if ($inventoryAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $totalValue,
                    'description' => 'Pengurangan persediaan dari penyesuaian stok',
                ]);
            }
        }
    }

    /**
     * Search product for adjustment input
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
     * Update stock based on adjustment
     */
    private function updateStock($productId, $warehouseId, $qty, $type, $adjustmentId, $code)
    {
        $product = Product::find($productId);

        // Get or Create ProductWarehouse
        $productWarehouse = ProductWarehouse::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['stock' => 0]
        );

        $previousStock = $productWarehouse->stock;

        if ($type === 'in') {
            $productWarehouse->stock += $qty;
        } else {
            $productWarehouse->stock -= $qty;
        }

        $productWarehouse->save();

        // Log stock mutation
        ProductStock::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'type' => $type,
            'qty' => $qty,
            'previous_stock' => $previousStock,
            'current_stock' => $productWarehouse->stock,
            'transaction_id' => null,
            'user_id' => auth()->id(),
            'note' => 'Stock Adjustment ' . $code,
        ]);
    }

    /**
     * Revert stock changes
     */
    private function revertStock($productId, $warehouseId, $qty, $type)
    {
        $productWarehouse = ProductWarehouse::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($productWarehouse) {
            if ($type === 'in') {
                $productWarehouse->stock -= $qty;
            } else {
                $productWarehouse->stock += $qty;
            }
            $productWarehouse->save();
        }
    }
}
