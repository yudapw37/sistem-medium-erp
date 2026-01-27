<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\StockOpname;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StockOpnameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $opnames = StockOpname::with(['warehouse', 'user'])
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

        return Inertia::render('Dashboard/StockOpname/Index', [
            'opnames' => $opnames,
            'warehouses' => $warehouses,
            'filters' => [
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

        return Inertia::render('Dashboard/StockOpname/Create', [
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.system_stock' => 'required|integer',
            'items.*.physical_stock' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate code
            $code = 'SO-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $opname = StockOpname::create([
                'code' => $code,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
                'status' => 'draft',
            ]);

            foreach ($request->items as $item) {
                $difference = $item['physical_stock'] - $item['system_stock'];
                
                $opname->details()->create([
                    'product_id' => $item['product_id'],
                    'current_stock' => $item['current_stock'] ?? 0,
                    'system_stock' => $item['system_stock'],
                    'physical_stock' => $item['physical_stock'],
                    'difference' => $difference,
                ]);
            }
        });

        return redirect()->route('stock-opnames.index')->with('success', 'Stock opname created as draft. Please finalize to update stock.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $opname = StockOpname::with(['warehouse', 'user', 'details.product'])->findOrFail($id);

        return Inertia::render('Dashboard/StockOpname/Show', [
            'opname' => $opname,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $opname = StockOpname::with(['details.product'])->findOrFail($id);

        if ($opname->status === 'finalized') {
            return redirect()->route('stock-opnames.index')->with('error', 'Cannot edit finalized stock opname.');
        }

        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/StockOpname/Edit', [
            'opname' => $opname,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $opname = StockOpname::with('details')->findOrFail($id);

        if ($opname->status === 'finalized') {
            return redirect()->route('stock-opnames.index')->with('error', 'Cannot edit finalized stock opname.');
        }

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.system_stock' => 'required|integer',
            'items.*.physical_stock' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $opname) {
            // Update opname
            $opname->update([
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);

            // Delete old details
            $opname->details()->delete();

            // Create new details
            foreach ($request->items as $item) {
                $difference = $item['physical_stock'] - $item['system_stock'];
                
                $opname->details()->create([
                    'product_id' => $item['product_id'],
                    'current_stock' => $item['current_stock'] ?? 0,
                    'system_stock' => $item['system_stock'],
                    'physical_stock' => $item['physical_stock'],
                    'difference' => $difference,
                ]);
            }
        });

        return redirect()->route('stock-opnames.index')->with('success', 'Stock opname updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $opname = StockOpname::with('details')->findOrFail($id);

        if ($opname->status === 'finalized') {
            return redirect()->route('stock-opnames.index')->with('error', 'Cannot delete finalized stock opname.');
        }

        DB::transaction(function () use ($opname) {
            $opname->details()->delete();
            $opname->delete();
        });

        return redirect()->route('stock-opnames.index')->with('success', 'Stock opname deleted successfully.');
    }

    /**
     * Finalize stock opname and update stock
     */
    public function finalize($id)
    {
        $opname = StockOpname::with(['details.product'])->findOrFail($id);

        if ($opname->status === 'finalized') {
            return redirect()->route('stock-opnames.index')->with('error', 'Stock opname already finalized.');
        }

        DB::transaction(function () use ($opname) {
            $totalSelisihLebih = 0;  // Fisik > Sistem
            $totalSelisihKurang = 0; // Fisik < Sistem

            foreach ($opname->details as $detail) {
                // Get or Create ProductWarehouse
                $productWarehouse = ProductWarehouse::firstOrCreate(
                    ['product_id' => $detail->product_id, 'warehouse_id' => $opname->warehouse_id],
                    ['stock' => 0, 'stock_fix' => 0]
                );

                $previousStock = $productWarehouse->stock_fix;

                // Calculate difference value
                if ($detail->difference != 0) {
                    $itemValue = abs($detail->difference) * ($detail->product->buy_price ?? 0);
                    
                    if ($detail->difference > 0) {
                        // Fisik > Sistem (selisih lebih)
                        $totalSelisihLebih += $itemValue;
                    } else {
                        // Fisik < Sistem (selisih kurang)
                        $totalSelisihKurang += $itemValue;
                    }
                }

                // Update both stock and stock_fix based on physical stock
                $productWarehouse->stock = $detail->physical_stock;
                $productWarehouse->stock_fix = $detail->physical_stock;
                $productWarehouse->save();

                // Log stock mutation only if there's a difference
                if ($detail->difference != 0) {
                    $type = $detail->difference > 0 ? 'in' : 'out';
                    $qty = abs($detail->difference);

                    ProductStock::create([
                        'product_id' => $detail->product_id,
                        'warehouse_id' => $opname->warehouse_id,
                        'type' => $type,
                        'qty' => $qty,
                        'previous_stock' => $previousStock,
                        'current_stock' => $productWarehouse->stock_fix,
                        'transaction_id' => null,
                        'sale_id' => null,
                        'purchase_id' => null,
                        'stock_opname_id' => $opname->id,
                        'user_id' => auth()->id(),
                        'note' => 'Stock Opname ' . $opname->code,
                    ]);
                }
            }

            // Create accounting journal if there's any difference
            if ($totalSelisihLebih > 0 || $totalSelisihKurang > 0) {
                $this->createOpnameJournal($opname, $totalSelisihLebih, $totalSelisihKurang);
            }

            $opname->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);
        });

        return redirect()->route('stock-opnames.index')->with('success', 'Stock opname berhasil difinalisasi dengan jurnal akuntansi.');
    }

    /**
     * Create accounting journal for stock opname
     */
    private function createOpnameJournal($opname, $totalSelisihLebih, $totalSelisihKurang)
    {
        // Get account settings
        $inventoryAccountId = \App\Models\AccountSetting::getAccountId('inventory');
        $stockAdjustmentOutAccountId = \App\Models\AccountSetting::getAccountId('stock_adjustment_out'); // Beban selisih
        $stockAdjustmentInAccountId = \App\Models\AccountSetting::getAccountId('stock_adjustment_in'); // Pendapatan lain

        // Fallback accounts - use available accounts as fallback
        if (!$stockAdjustmentOutAccountId) {
            $stockAdjustmentOutAccountId = \App\Models\AccountSetting::getAccountId('cogs');
        }
        if (!$stockAdjustmentInAccountId) {
            $stockAdjustmentInAccountId = \App\Models\AccountSetting::getAccountId('other_income');
            // Second fallback to sales if other_income doesn't exist
            if (!$stockAdjustmentInAccountId) {
                $stockAdjustmentInAccountId = \App\Models\AccountSetting::getAccountId('sales');
            }
        }

        // Create journal header
        $journal = \App\Models\Journal::create([
            'date' => $opname->date,
            'reference' => \App\Models\Journal::generateReference('SO'),
            'description' => 'Selisih Stock Opname ' . $opname->code,
            'source_type' => StockOpname::class,
            'source_id' => $opname->id,
            'user_id' => auth()->id(),
        ]);

        // Journal for Selisih Kurang (Fisik < Sistem) - Barang hilang/rusak
        // Debit: Beban Selisih, Credit: Persediaan
        if ($totalSelisihKurang > 0) {
            if ($stockAdjustmentOutAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $stockAdjustmentOutAccountId,
                    'debit' => $totalSelisihKurang,
                    'credit' => 0,
                    'description' => 'Beban selisih persediaan kurang',
                ]);
            }
            if ($inventoryAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $totalSelisihKurang,
                    'description' => 'Pengurangan persediaan dari selisih opname',
                ]);
            }
        }

        // Journal for Selisih Lebih (Fisik > Sistem) - Barang ditemukan
        // Debit: Persediaan, Credit: Pendapatan Lain
        if ($totalSelisihLebih > 0) {
            if ($inventoryAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $inventoryAccountId,
                    'debit' => $totalSelisihLebih,
                    'credit' => 0,
                    'description' => 'Penambahan persediaan dari selisih opname',
                ]);
            }
            if ($stockAdjustmentInAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $stockAdjustmentInAccountId,
                    'debit' => 0,
                    'credit' => $totalSelisihLebih,
                    'description' => 'Pendapatan dari selisih persediaan lebih',
                ]);
            }
        }
    }

    /**
     * Search product for opname input
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
     * Get product stock in specific warehouse
     */
    public function getProductStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $productWarehouse = ProductWarehouse::where('product_id', $request->product_id)
            ->where('warehouse_id', $request->warehouse_id)
            ->first();

        $systemStock = $productWarehouse ? $productWarehouse->stock_fix : 0;

        return response()->json([
            'system_stock' => $systemStock,
        ]);
    }

    /**
     * Get all products with stock for a warehouse
     */
    public function getWarehouseProducts(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $products = Product::with(['category'])
            ->orderBy('title')
            ->get()
            ->map(function ($product) use ($request) {
                $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->first();

                return [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'barcode' => $product->barcode,
                    'category' => $product->category?->name ?? '-',
                    'current_stock' => $productWarehouse ? $productWarehouse->stock : 0,
                    'system_stock' => $productWarehouse ? $productWarehouse->stock_fix : 0,
                    'physical_stock' => $productWarehouse ? $productWarehouse->stock_fix : 0, // Default to system stock
                ];
            });

        return response()->json($products);
    }
}
