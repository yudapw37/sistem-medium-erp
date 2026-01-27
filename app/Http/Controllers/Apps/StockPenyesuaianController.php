<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\StockPenyesuaian;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StockPenyesuaianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $penyesuaians = StockPenyesuaian::with(['warehouse', 'user'])
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->warehouse_id, function ($query, $warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('code', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/StockPenyesuaian/Index', [
            'penyesuaians' => $penyesuaians,
            'warehouses' => $warehouses,
            'filters' => [
                'type' => $request->type,
                'warehouse_id' => $request->warehouse_id,
                'status' => $request->status,
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

        return Inertia::render('Dashboard/StockPenyesuaian/Create', [
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
            $code = 'SADJ-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $penyesuaian = StockPenyesuaian::create([
                'code' => $code,
                'type' => $request->type,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
                'status' => 'draft',
            ]);

            foreach ($request->items as $item) {
                $penyesuaian->details()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);
            }
        });

        return redirect()->route('stock-penyesuaian.index')->with('success', 'Stock penyesuaian berhasil dibuat sebagai draft.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $penyesuaian = StockPenyesuaian::with(['warehouse', 'user', 'details.product'])->findOrFail($id);

        return Inertia::render('Dashboard/StockPenyesuaian/Show', [
            'penyesuaian' => $penyesuaian,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $penyesuaian = StockPenyesuaian::with(['details.product'])->findOrFail($id);

        if ($penyesuaian->isFinalized()) {
            return redirect()->route('stock-penyesuaian.index')->with('error', 'Tidak dapat mengedit penyesuaian yang sudah difinalisasi.');
        }

        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/StockPenyesuaian/Edit', [
            'penyesuaian' => $penyesuaian,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $penyesuaian = StockPenyesuaian::with('details')->findOrFail($id);

        if ($penyesuaian->isFinalized()) {
            return redirect()->route('stock-penyesuaian.index')->with('error', 'Tidak dapat mengedit penyesuaian yang sudah difinalisasi.');
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

        DB::transaction(function () use ($request, $penyesuaian) {
            $penyesuaian->update([
                'type' => $request->type,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);

            $penyesuaian->details()->delete();

            foreach ($request->items as $item) {
                $penyesuaian->details()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);
            }
        });

        return redirect()->route('stock-penyesuaian.index')->with('success', 'Stock penyesuaian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $penyesuaian = StockPenyesuaian::with('details')->findOrFail($id);

        if ($penyesuaian->isFinalized()) {
            return redirect()->route('stock-penyesuaian.index')->with('error', 'Tidak dapat menghapus penyesuaian yang sudah difinalisasi.');
        }

        DB::transaction(function () use ($penyesuaian) {
            $penyesuaian->details()->delete();
            $penyesuaian->delete();
        });

        return redirect()->route('stock-penyesuaian.index')->with('success', 'Stock penyesuaian berhasil dihapus.');
    }

    /**
     * Finalize penyesuaian and update stock with accounting journal
     */
    public function finalize($id)
    {
        $penyesuaian = StockPenyesuaian::with(['details.product'])->findOrFail($id);

        if ($penyesuaian->isFinalized()) {
            return redirect()->route('stock-penyesuaian.index')->with('error', 'Penyesuaian sudah difinalisasi.');
        }

        DB::transaction(function () use ($penyesuaian) {
            $totalValue = 0;

            foreach ($penyesuaian->details as $detail) {
                // Get or Create ProductWarehouse
                $productWarehouse = ProductWarehouse::firstOrCreate(
                    ['product_id' => $detail->product_id, 'warehouse_id' => $penyesuaian->warehouse_id],
                    ['stock' => 0, 'stock_fix' => 0]
                );

                // Calculate value using buy_price
                $itemValue = ($detail->product->buy_price ?? 0) * $detail->qty;
                $totalValue += $itemValue;

                $previousStock = $productWarehouse->stock_fix;

                // Update stock and stock_fix
                if ($penyesuaian->type === 'in') {
                    $productWarehouse->stock += $detail->qty;
                    $productWarehouse->stock_fix += $detail->qty;
                } else {
                    $productWarehouse->stock -= $detail->qty;
                    $productWarehouse->stock_fix -= $detail->qty;
                }

                $productWarehouse->save();

                // Log stock mutation
                ProductStock::create([
                    'product_id' => $detail->product_id,
                    'warehouse_id' => $penyesuaian->warehouse_id,
                    'type' => $penyesuaian->type,
                    'qty' => $detail->qty,
                    'previous_stock' => $previousStock,
                    'current_stock' => $productWarehouse->stock_fix,
                    'user_id' => auth()->id(),
                    'note' => 'Stock Penyesuaian ' . $penyesuaian->code,
                ]);
            }

            // Create accounting journal if value > 0
            if ($totalValue > 0) {
                $this->createJournal($penyesuaian, $totalValue);
            }

            $penyesuaian->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);
        });

        return redirect()->route('stock-penyesuaian.index')->with('success', 'Stock penyesuaian berhasil difinalisasi dengan jurnal akuntansi.');
    }

    /**
     * Create accounting journal for stock penyesuaian
     */
    private function createJournal($penyesuaian, $totalValue)
    {
        // Get account settings
        $inventoryAccountId = \App\Models\AccountSetting::getAccountId('inventory');
        $stockAdjustmentInAccountId = \App\Models\AccountSetting::getAccountId('stock_adjustment_in');
        $stockAdjustmentOutAccountId = \App\Models\AccountSetting::getAccountId('stock_adjustment_out');

        // Fallback to available accounts if not set
        if (!$stockAdjustmentInAccountId) {
            $stockAdjustmentInAccountId = \App\Models\AccountSetting::getAccountId('equity');
            // Second fallback to sales if equity doesn't exist
            if (!$stockAdjustmentInAccountId) {
                $stockAdjustmentInAccountId = \App\Models\AccountSetting::getAccountId('sales');
            }
        }
        if (!$stockAdjustmentOutAccountId) {
            $stockAdjustmentOutAccountId = \App\Models\AccountSetting::getAccountId('cogs');
        }

        // Create journal header
        $journal = \App\Models\Journal::create([
            'date' => $penyesuaian->date,
            'reference' => \App\Models\Journal::generateReference('SADJ'),
            'description' => 'Penyesuaian Stok ' . $penyesuaian->code . ' (' . ($penyesuaian->type === 'in' ? 'Masuk' : 'Keluar') . ')',
            'source_type' => StockPenyesuaian::class,
            'source_id' => $penyesuaian->id,
            'user_id' => auth()->id(),
        ]);

        // Create journal entries
        if ($penyesuaian->type === 'in') {
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
     * Search product for penyesuaian input
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
