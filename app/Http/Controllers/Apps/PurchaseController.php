<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'warehouse'])
            ->when($request->q, function ($query, $q) {
                $query->where('invoice', 'like', '%' . $q . '%');
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/Purchases/Index', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/Purchases/Create', [
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
            'warehouse_id' => 'required|exists:warehouses,id',
            'grand_total' => 'required|integer|min:0',
            'payment_type' => 'required|in:cash,tempo',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.buy_price'  => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate Invoice
            $length = 10;
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
            }
            $invoice = 'PO-' . Str::upper($random);

            $purchase = Purchase::create([
                'invoice'      => $invoice,
                'supplier_id'  => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'user_id'      => auth()->id(),
                'grand_total'  => $request->grand_total,
                'payment_type' => $request->payment_type,
                'status'       => 'draft', // Save as draft
            ]);

            foreach ($request->items as $item) {
                // Create Detail
                $purchase->details()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'buy_price'  => $item['buy_price'],
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase created as draft. Please finalize to update stock.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'warehouse', 'details.product', 'user'])->findOrFail($id);

        return Inertia::render('Dashboard/Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $purchase = Purchase::with(['details.product'])->findOrFail($id);

        if ($purchase->status === 'finalized') {
            return redirect()->route('purchases.index')->with('error', 'Cannot edit finalized purchase.');
        }

        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/Purchases/Edit', [
            'purchase' => $purchase,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        if ($purchase->status === 'finalized') {
            return redirect()->route('purchases.index')->with('error', 'Cannot edit finalized purchase.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'grand_total' => 'required|integer|min:0',
            'payment_type' => 'required|in:cash,tempo',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.buy_price'  => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            $purchase->update([
                'supplier_id'  => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'grand_total'  => $request->grand_total,
                'payment_type' => $request->payment_type,
            ]);

            // Delete old details
            $purchase->details()->delete();

            // Create new details
            foreach ($request->items as $item) {
                $purchase->details()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'buy_price'  => $item['buy_price'],
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);

        if ($purchase->status === 'finalized') {
            return redirect()->route('purchases.index')->with('error', 'Cannot delete finalized purchase.');
        }

        $purchase->details()->delete();
        $purchase->delete();

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
    }

    /**
     * Finalize purchase and update stock.
     */
    public function finalize($id)
    {
        $purchase = Purchase::with('details')->findOrFail($id);

        if ($purchase->status === 'finalized') {
            return redirect()->route('purchases.index')->with('error', 'Purchase already finalized.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->details as $detail) {
                $product = Product::find($detail->product_id);

                // Get or Create ProductWarehouse
                $productWarehouse = ProductWarehouse::firstOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $purchase->warehouse_id],
                    ['stock' => 0]
                );

                $previous_stock = $productWarehouse->stock;
                $productWarehouse->stock += $detail->qty;
                $productWarehouse->save();

                $product->buy_price = $detail->buy_price;
                $product->save();

                // Log Stock Mutation
                ProductStock::create([
                    'product_id'     => $product->id,
                    'warehouse_id'   => $purchase->warehouse_id,
                    'type'           => 'in',
                    'qty'            => $detail->qty,
                    'previous_stock' => $previous_stock,
                    'current_stock'  => $productWarehouse->stock,
                    'transaction_id' => null,
                    'user_id'        => auth()->id(),
                    'purchase_id'    => $purchase->id,
                    'note'           => 'Purchase ' . $purchase->invoice . ' finalized',
                ]);
            }

            // Create accounting journal
            $this->createPurchaseJournal($purchase);

            $purchase->update([
                'status'       => 'finalized',
                'finalized_at' => now(),
            ]);

            // Check and fulfill waiting Pre-Orders
            $this->fulfillPreOrders($purchase->warehouse_id);
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase finalized and applicable Pre-Orders updated.');
    }

    /**
     * Check all waiting Pre-Orders in a warehouse and fulfill them if stock is now sufficient.
     */
    private function fulfillPreOrders($warehouseId)
    {
        $waitingSales = \App\Models\Sale::with(['details.product', 'details.bundle.items.product'])
            ->where('warehouse_id', $warehouseId)
            ->where('is_preorder', true)
            ->where('preorder_status', 'pending')
            ->where('approval_status', 'waiting_stock')
            ->get();

        foreach ($waitingSales as $sale) {
            $allReady = true;

            foreach ($sale->details as $detail) {
                if ($detail->product_id) {
                    $pw = ProductWarehouse::where('product_id', $detail->product_id)
                        ->where('warehouse_id', $warehouseId)
                        ->first();
                    
                    if (!$pw || $pw->stock < $detail->qty) {
                        $allReady = false;
                        break;
                    }
                } elseif ($detail->bundle_id) {
                    foreach ($detail->bundle->items as $bundleItem) {
                        $pw = ProductWarehouse::where('product_id', $bundleItem->product_id)
                            ->where('warehouse_id', $warehouseId)
                            ->first();
                        
                        $needed = $bundleItem->qty * $detail->qty;
                        if (!$pw || $pw->stock < $needed) {
                            $allReady = false;
                            break 2;
                        }
                    }
                }
            }

            if ($allReady) {
                $updateData = ['preorder_status' => 'ready'];
                
                // If paid amount is equal or more than grand total, move to pending_warehouse
                if ($sale->paid_amount >= $sale->grand_total) {
                    $updateData['approval_status'] = 'pending_warehouse';
                }
                
                $sale->update($updateData);
            }
        }
    }

    /**
     * Create accounting journal for purchase
     */
    private function createPurchaseJournal($purchase)
    {
        $totalAmount = $purchase->grand_total;
        if ($totalAmount <= 0) {
            return;
        }

        // Get account settings
        $inventoryAccountId = \App\Models\AccountSetting::getAccountId('inventory');
        $cashAccountId = \App\Models\AccountSetting::getAccountId('cash');
        $apAccountId = \App\Models\AccountSetting::getAccountId('accounts_payable');

        // Create journal header
        $journal = \App\Models\Journal::create([
            'date' => $purchase->created_at->toDateString(),
            'reference' => \App\Models\Journal::generateReference('PUR'),
            'description' => 'Pembelian ' . $purchase->invoice . ' - ' . ($purchase->supplier?->name ?? 'Supplier'),
            'source_type' => Purchase::class,
            'source_id' => $purchase->id,
            'user_id' => auth()->id(),
        ]);

        // Debit: Persediaan/Inventory
        if ($inventoryAccountId) {
            \App\Models\JournalEntry::create([
                'journal_id' => $journal->id,
                'account_id' => $inventoryAccountId,
                'debit' => $totalAmount,
                'credit' => 0,
                'description' => 'Pembelian barang dagangan',
            ]);
        }

        // Credit: Kas (tunai) atau Hutang (kredit)
        if ($purchase->payment_type === 'credit' && $apAccountId) {
            // Kredit: Credit Accounts Payable
            \App\Models\JournalEntry::create([
                'journal_id' => $journal->id,
                'account_id' => $apAccountId,
                'debit' => 0,
                'credit' => $totalAmount,
                'description' => 'Hutang pembelian ke supplier',
            ]);
        } elseif ($cashAccountId) {
            // Tunai: Credit Cash
            \App\Models\JournalEntry::create([
                'journal_id' => $journal->id,
                'account_id' => $cashAccountId,
                'debit' => 0,
                'credit' => $totalAmount,
                'description' => 'Pembayaran tunai pembelian',
            ]);
        }
    }
    
    /**
     * Search product for purchase input
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
     * Export purchases to Excel (XLSX format)
     */
    public function exportExcel(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'warehouse', 'details.product', 'user'])
            ->when($request->q, function ($query, $q) {
                $query->where('invoice', 'like', '%' . $q . '%');
            })
            ->latest()
            ->get();

        // Prepare data for XLSX
        $data = [
            // Header row with styling
            ['<b>No</b>', '<b>Invoice</b>', '<b>Tanggal</b>', '<b>Supplier</b>', '<b>Gudang</b>', '<b>Status</b>', '<b>Produk</b>', '<b>Qty</b>', '<b>Harga Beli</b>', '<b>Subtotal</b>', '<b>Grand Total</b>', '<b>User</b>']
        ];

        // Data rows
        $no = 1;
        foreach ($purchases as $purchase) {
            foreach ($purchase->details as $index => $detail) {
                $data[] = [
                    $no++,
                    $purchase->invoice,
                    $purchase->created_at->format('d/m/Y H:i'),
                    $purchase->supplier->name ?? '-',
                    $purchase->warehouse->name ?? '-',
                    $purchase->status === 'finalized' ? 'Finalized' : 'Draft',
                    $detail->product->title ?? '-',
                    $detail->qty,
                    'Rp ' . number_format($detail->buy_price, 0, ',', '.'),
                    'Rp ' . number_format($detail->qty * $detail->buy_price, 0, ',', '.'),
                    $index === 0 ? 'Rp ' . number_format($purchase->grand_total, 0, ',', '.') : '',
                    $purchase->user->name ?? '-',
                ];
            }
        }

        $filename = 'purchases_' . date('Y-m-d_His') . '.xlsx';
        
        // Generate XLSX file
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
        $xlsx->downloadAs($filename);
        
        exit;
    }

    /**
     * Export purchases to PDF (HTML print view)
     */
    public function exportPdf(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'warehouse', 'details.product', 'user'])
            ->when($request->q, function ($query, $q) {
                $query->where('invoice', 'like', '%' . $q . '%');
            })
            ->latest()
            ->get();

        return view('exports.purchases-pdf', compact('purchases'));
    }
}
