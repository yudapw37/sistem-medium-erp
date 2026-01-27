<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\ZeroValueTransaction;
use App\Models\ZeroValueTransactionDetail;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ZeroValueTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = ZeroValueTransaction::with(['warehouse', 'user'])
            ->when($request->search, function ($query, $search) {
                $query->where('code', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->reason, function ($query, $reason) {
                $query->where('reason', $reason);
            })
            ->when($request->warehouse_id, function ($query, $warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/ZeroValueTransactions/Index', [
            'transactions' => $transactions,
            'warehouses' => Warehouse::all(),
            'reasonLabels' => ZeroValueTransaction::REASON_LABELS,
            'filters' => $request->only(['search', 'type', 'reason', 'warehouse_id', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/ZeroValueTransactions/Create', [
            'warehouses' => Warehouse::all(),
            'reasonLabels' => ZeroValueTransaction::REASON_LABELS,
            'outReasons' => ZeroValueTransaction::OUT_REASONS,
            'inReasons' => ZeroValueTransaction::IN_REASONS,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'reason' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.buy_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $transaction = ZeroValueTransaction::create([
                'code' => ZeroValueTransaction::generateCode(),
                'type' => $request->type,
                'reason' => $request->reason,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'notes' => $request->notes,
                'status' => 'draft',
                'user_id' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                ZeroValueTransactionDetail::create([
                    'zero_value_transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'buy_price' => $item['buy_price'],
                ]);
            }
        });

        return redirect()->route('zero-value-transactions.index')
            ->with('success', 'Transaksi berhasil dibuat sebagai draft.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $transaction = ZeroValueTransaction::with(['warehouse', 'user', 'details.product'])
            ->findOrFail($id);

        return Inertia::render('Dashboard/ZeroValueTransactions/Show', [
            'transaction' => $transaction,
            'reasonLabels' => ZeroValueTransaction::REASON_LABELS,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $transaction = ZeroValueTransaction::with(['details.product'])
            ->findOrFail($id);

        if ($transaction->isFinalized()) {
            return redirect()->route('zero-value-transactions.index')
                ->with('error', 'Transaksi yang sudah difinalisasi tidak dapat diedit.');
        }

        return Inertia::render('Dashboard/ZeroValueTransactions/Edit', [
            'transaction' => $transaction,
            'warehouses' => Warehouse::all(),
            'reasonLabels' => ZeroValueTransaction::REASON_LABELS,
            'outReasons' => ZeroValueTransaction::OUT_REASONS,
            'inReasons' => ZeroValueTransaction::IN_REASONS,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $transaction = ZeroValueTransaction::findOrFail($id);

        if ($transaction->isFinalized()) {
            return redirect()->route('zero-value-transactions.index')
                ->with('error', 'Transaksi yang sudah difinalisasi tidak dapat diedit.');
        }

        $request->validate([
            'type' => 'required|in:in,out',
            'reason' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.buy_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $transaction) {
            $transaction->update([
                'type' => $request->type,
                'reason' => $request->reason,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);

            // Delete existing details and recreate
            $transaction->details()->delete();

            foreach ($request->items as $item) {
                ZeroValueTransactionDetail::create([
                    'zero_value_transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'buy_price' => $item['buy_price'],
                ]);
            }
        });

        return redirect()->route('zero-value-transactions.index')
            ->with('success', 'Transaksi berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $transaction = ZeroValueTransaction::findOrFail($id);

        if ($transaction->isFinalized()) {
            return redirect()->route('zero-value-transactions.index')
                ->with('error', 'Transaksi yang sudah difinalisasi tidak dapat dihapus.');
        }

        $transaction->details()->delete();
        $transaction->delete();

        return redirect()->route('zero-value-transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Finalize transaction and update stock
     */
    public function finalize($id)
    {
        $transaction = ZeroValueTransaction::with(['details.product'])->findOrFail($id);

        if ($transaction->isFinalized()) {
            return redirect()->route('zero-value-transactions.index')
                ->with('error', 'Transaksi sudah difinalisasi.');
        }

        DB::transaction(function () use ($transaction) {
            $totalValue = 0;

            foreach ($transaction->details as $detail) {
                // Get or Create ProductWarehouse
                $productWarehouse = ProductWarehouse::firstOrCreate(
                    ['product_id' => $detail->product_id, 'warehouse_id' => $transaction->warehouse_id],
                    ['stock' => 0, 'stock_fix' => 0]
                );

                // Calculate value
                $itemValue = $detail->buy_price * $detail->qty;
                $totalValue += $itemValue;

                // Update stock_fix based on type
                $previousStock = $productWarehouse->stock_fix;
                
                if ($transaction->type === 'in') {
                    // Stok masuk (bonus, hadiah, dll)
                    $productWarehouse->stock_fix += $detail->qty;
                } else {
                    // Stok keluar (rusak, expired, hibah, dll)
                    $productWarehouse->stock_fix -= $detail->qty;
                }

                $productWarehouse->save();

                // Create product stock mutation record
                ProductStock::create([
                    'product_id' => $detail->product_id,
                    'warehouse_id' => $transaction->warehouse_id,
                    'qty' => $transaction->type === 'in' ? $detail->qty : -$detail->qty,
                    'type' => $transaction->type === 'in' ? 'in' : 'out',
                    'previous_stock' => $previousStock,
                    'current_stock' => $productWarehouse->stock_fix,
                    'user_id' => auth()->id(),
                    'note' => 'Zero-Value Transaction: ' . $transaction->code . ' - ' . ($transaction->reason_label ?? $transaction->reason),
                ]);
            }

            // Create accounting journal if value > 0
            if ($totalValue > 0) {
                $this->createJournal($transaction, $totalValue);
            }

            $transaction->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);
        });

        return redirect()->route('zero-value-transactions.index')
            ->with('success', 'Transaksi berhasil difinalisasi dengan jurnal akuntansi.');
    }

    /**
     * Create accounting journal for zero value transaction
     */
    private function createJournal($transaction, $totalValue)
    {
        // Get account settings
        $inventoryAccountId = \App\Models\AccountSetting::getAccountId('inventory');
        
        if ($transaction->type === 'out') {
            // Stok Keluar: Debit Expense (Kerugian), Credit Inventory
            $expenseAccountId = \App\Models\AccountSetting::getAccountId('stock_adjustment_out');
            if (!$expenseAccountId) {
                $expenseAccountId = \App\Models\AccountSetting::getAccountId('cogs');
            }

            $journal = \App\Models\Journal::create([
                'date' => $transaction->date,
                'reference' => \App\Models\Journal::generateReference('ZVT'),
                'description' => 'Zero-Value Transaction (Keluar) - ' . $transaction->code . ' - ' . ($transaction->reason_label ?? $transaction->reason),
                'source_type' => ZeroValueTransaction::class,
                'source_id' => $transaction->id,
                'user_id' => auth()->id(),
            ]);

            // Debit: Beban Kerugian
            if ($expenseAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $expenseAccountId,
                    'debit' => $totalValue,
                    'credit' => 0,
                    'description' => 'Beban kerugian barang (' . ($transaction->reason_label ?? $transaction->reason) . ')',
                ]);
            }

            // Credit: Persediaan
            if ($inventoryAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $totalValue,
                    'description' => 'Pengurangan persediaan (' . ($transaction->reason_label ?? $transaction->reason) . ')',
                ]);
            }
        } else {
            // Stok Masuk: Debit Inventory, Credit Pendapatan Lain-lain
            $incomeAccountId = \App\Models\AccountSetting::getAccountId('other_income');
            if (!$incomeAccountId) {
                $incomeAccountId = \App\Models\AccountSetting::getAccountId('stock_adjustment_in');
            }
            if (!$incomeAccountId) {
                $incomeAccountId = \App\Models\AccountSetting::getAccountId('equity');
            }

            $journal = \App\Models\Journal::create([
                'date' => $transaction->date,
                'reference' => \App\Models\Journal::generateReference('ZVT'),
                'description' => 'Zero-Value Transaction (Masuk) - ' . $transaction->code . ' - ' . ($transaction->reason_label ?? $transaction->reason),
                'source_type' => ZeroValueTransaction::class,
                'source_id' => $transaction->id,
                'user_id' => auth()->id(),
            ]);

            // Debit: Persediaan
            if ($inventoryAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $inventoryAccountId,
                    'debit' => $totalValue,
                    'credit' => 0,
                    'description' => 'Penambahan persediaan (' . ($transaction->reason_label ?? $transaction->reason) . ')',
                ]);
            }

            // Credit: Pendapatan Lain-lain
            if ($incomeAccountId) {
                \App\Models\JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $incomeAccountId,
                    'debit' => 0,
                    'credit' => $totalValue,
                    'description' => 'Pendapatan dari barang bonus/hadiah (' . ($transaction->reason_label ?? $transaction->reason) . ')',
                ]);
            }
        }
    }

    /**
     * Search product for transaction input
     */
    public function searchProduct(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $products = Product::with(['category'])
            ->select('products.*', 'product_warehouses.stock as warehouse_stock')
            ->join('product_warehouses', 'products.id', '=', 'product_warehouses.product_id')
            ->where('product_warehouses.warehouse_id', $request->warehouse_id)
            ->where('product_warehouses.stock', '>=', 0)
            ->where(function($query) use ($request) {
                $query->where('products.title', 'like', '%' . $request->search . '%')
                      ->orWhere('products.barcode', 'like', '%' . $request->search . '%');
            })
            ->limit(10)
            ->get();

        return response()->json($products);
    }
}
