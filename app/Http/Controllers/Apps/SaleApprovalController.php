<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\Sale;
use App\Models\SaleApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Services\JournalService;

class SaleApprovalController extends Controller
{
    /**
     * Display list of sales pending finance approval
     */
    public function financeIndex(Request $request)
    {
        $sales = Sale::with(['customer', 'warehouse', 'user', 'details'])
            ->where('approval_status', 'pending_finance')
            ->when($request->q, function ($query) use ($request) {
                $query->where('invoice', 'like', '%' . $request->q . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/Approvals/Finance', [
            'sales' => $sales,
            'filters' => $request->only('q'),
        ]);
    }

    /**
     * Approve sale by finance
     */
    public function financeApprove(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $sale = Sale::findOrFail($id);

        if ($sale->approval_status !== 'pending_finance') {
            return redirect()->back()->with('error', 'Transaksi tidak dalam status menunggu approval finance.');
        }

        DB::transaction(function () use ($sale, $request) {
            // Create approval record
            SaleApproval::create([
                'sale_id' => $sale->id,
                'type' => 'finance',
                'status' => 'approved',
                'user_id' => auth()->id(),
                'notes' => $request->notes,
                'approved_at' => now(),
            ]);

            // Create journal entries after finance approval
            $saleWithDetails = Sale::with(['details.product', 'details.bundle.items.product'])->find($sale->id);
            JournalService::createSaleJournal($saleWithDetails);

            // Update sale status
            $nextStatus = $sale->is_preorder ? 'waiting_stock' : 'pending_warehouse';
            
            $sale->update([
                'approval_status' => $nextStatus,
                'rejection_notes' => null,
            ]);
        });

        $msg = $sale->is_preorder 
            ? 'Transaksi berhasil di-approve (Pre-Order) dan masuk antrian Menunggu Stok.' 
            : 'Transaksi berhasil di-approve dan diteruskan ke Gudang.';

        return redirect()->route('approvals.finance.index')->with('success', $msg);
    }

    /**
     * Reject sale by finance
     */
    public function financeReject(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $sale = Sale::with('details.product')->findOrFail($id);

        if ($sale->approval_status !== 'pending_finance') {
            return redirect()->back()->with('error', 'Transaksi tidak dalam status menunggu approval finance.');
        }

        // Safety: Check if payments exist
        if ($sale->payments()->count() > 0) {
            return redirect()->back()->with('error', 'Transaksi tidak dapat ditolak karena sudah memiliki data pembayaran.');
        }

        DB::transaction(function () use ($sale, $request) {
            // Create rejection record
            SaleApproval::create([
                'sale_id' => $sale->id,
                'type' => 'finance',
                'status' => 'rejected',
                'user_id' => auth()->id(),
                'notes' => $request->notes,
                'approved_at' => now(),
            ]);

            // Return stock
            $this->returnStock($sale);

            // Update sale status
            $sale->update([
                'approval_status' => 'rejected',
                'rejection_notes' => '[Finance] ' . $request->notes,
                'status' => 'draft',
            ]);
        });

        return redirect()->route('approvals.finance.index')->with('success', 'Transaksi ditolak dan stok telah dikembalikan.');
    }

    /**
     * Display list of sales pending warehouse approval
     */
    public function warehouseIndex(Request $request)
    {
        $sales = Sale::with(['customer', 'warehouse', 'user', 'details', 'financeApproval.user'])
            ->where('approval_status', 'pending_warehouse')
            ->when($request->q, function ($query) use ($request) {
                $query->where('invoice', 'like', '%' . $request->q . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/Approvals/Warehouse', [
            'sales' => $sales,
            'filters' => $request->only('q'),
        ]);
    }

    /**
     * Approve sale by warehouse
     */
    public function warehouseApprove(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $sale = Sale::findOrFail($id);

        if ($sale->approval_status !== 'pending_warehouse') {
            return redirect()->back()->with('error', 'Transaksi tidak dalam status menunggu approval gudang.');
        }

        DB::transaction(function () use ($sale, $request) {
            // Create approval record
            SaleApproval::create([
                'sale_id' => $sale->id,
                'type' => 'warehouse',
                'status' => 'approved',
                'user_id' => auth()->id(),
                'notes' => $request->notes,
                'approved_at' => now(),
            ]);

            // Update sale status to completed
            $sale->update([
                'approval_status' => 'completed',
                'rejection_notes' => null,
            ]);
        });

        return redirect()->route('approvals.warehouse.index')->with('success', 'Transaksi berhasil di-approve. Proses selesai.');
    }

    /**
     * Reject sale by warehouse
     */
    public function warehouseReject(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $sale = Sale::with('details.product')->findOrFail($id);

        if ($sale->approval_status !== 'pending_warehouse') {
            return redirect()->back()->with('error', 'Transaksi tidak dalam status menunggu approval gudang.');
        }

        // Safety: Check if payments exist
        if ($sale->payments()->count() > 0) {
            return redirect()->back()->with('error', 'Transaksi tidak dapat ditolak karena sudah memiliki data pembayaran. Silakan hapus pembayaran terlebih dahulu.');
        }

        DB::transaction(function () use ($sale, $request) {
            // Create rejection record
            SaleApproval::create([
                'sale_id' => $sale->id,
                'type' => 'warehouse',
                'status' => 'rejected',
                'user_id' => auth()->id(),
                'notes' => $request->notes,
                'approved_at' => now(),
            ]);

            // Reverse/Delete Journal if exists (from finance approval)
            if ($sale->journal) {
                // We delete the entries first if cascade is not on, but Journal model usually handles it or we delete the whole journal
                $sale->journal->entries()->delete();
                $sale->journal->delete();
            }

            // Return stock
            $this->returnStock($sale);

            // Update sale status
            $sale->update([
                'approval_status' => 'rejected',
                'rejection_notes' => '[Gudang] ' . $request->notes,
                'status' => 'draft',
            ]);
        });

        return redirect()->route('approvals.warehouse.index')->with('success', 'Transaksi ditolak dan stok telah dikembalikan.');
    }

    /**
     * Return stock when sale is rejected
     */
    private function returnStock(Sale $sale)
    {
        foreach ($sale->details as $detail) {
            if ($detail->product_id) {
                $product = $detail->product;
                $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->first();

                if ($productWarehouse) {
                    $previous_stock = $productWarehouse->stock;
                    $productWarehouse->stock += $detail->qty;
                    $productWarehouse->save();

                    // Log stock return
                    ProductStock::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $sale->warehouse_id,
                        'type' => 'in',
                        'qty' => $detail->qty,
                        'previous_stock' => $previous_stock,
                        'current_stock' => $productWarehouse->stock,
                        'user_id' => auth()->id(),
                        'sale_id' => $sale->id,
                        'note' => 'Sale ' . $sale->invoice . ' rejected - stock returned',
                    ]);
                }
            } elseif ($detail->bundle_id) {
                $bundle = ProductBundle::with('items.product')->find($detail->bundle_id);
                
                foreach ($bundle->items as $bundleItem) {
                    $product = $bundleItem->product;
                    $returnQty = $bundleItem->qty * $detail->qty;

                    $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                        ->where('warehouse_id', $sale->warehouse_id)
                        ->first();

                    if ($productWarehouse) {
                        $previous_stock = $productWarehouse->stock;
                        $productWarehouse->stock += $returnQty;
                        $productWarehouse->save();

                        ProductStock::create([
                            'product_id' => $product->id,
                            'warehouse_id' => $sale->warehouse_id,
                            'type' => 'in',
                            'qty' => $returnQty,
                            'previous_stock' => $previous_stock,
                            'current_stock' => $productWarehouse->stock,
                            'user_id' => auth()->id(),
                            'sale_id' => $sale->id,
                            'note' => 'Sale ' . $sale->invoice . ' rejected - bundle stock returned',
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Show sale detail for approval
     */
    public function show($id)
    {
        $sale = Sale::with([
            'customer',
            'warehouse',
            'user',
            'details.product',
            'details.bundle',
            'approvals.user',
        ])->findOrFail($id);

        return Inertia::render('Dashboard/Approvals/Show', [
            'sale' => $sale,
        ]);
    }

    /**
     * Display finance approval history
     */
    public function financeHistory(Request $request)
    {
        $approvals = SaleApproval::with(['sale.customer', 'sale.warehouse', 'user'])
            ->where('type', 'finance')
            ->whereIn('status', ['approved', 'rejected'])
            ->when($request->q, function ($query) use ($request) {
                $query->whereHas('sale', function ($q) use ($request) {
                    $q->where('invoice', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->start_date, function ($query) use ($request) {
                $query->whereDate('approved_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                $query->whereDate('approved_at', '<=', $request->end_date);
            })
            ->latest('approved_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/Approvals/FinanceHistory', [
            'approvals' => $approvals,
            'filters' => $request->only(['q', 'status', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Display warehouse approval history
     */
    public function warehouseHistory(Request $request)
    {
        $approvals = SaleApproval::with(['sale.customer', 'sale.warehouse', 'user'])
            ->where('type', 'warehouse')
            ->whereIn('status', ['approved', 'rejected'])
            ->when($request->q, function ($query) use ($request) {
                $query->whereHas('sale', function ($q) use ($request) {
                    $q->where('invoice', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->start_date, function ($query) use ($request) {
                $query->whereDate('approved_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                $query->whereDate('approved_at', '<=', $request->end_date);
            })
            ->latest('approved_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/Approvals/WarehouseHistory', [
            'approvals' => $approvals,
            'filters' => $request->only(['q', 'status', 'start_date', 'end_date']),
        ]);
    }
}
