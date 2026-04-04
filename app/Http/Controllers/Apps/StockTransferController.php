<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\AccountSetting;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'user'])
            ->when($request->q, fn($q, $s) => $q->where('code', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/StockTransfers/Index', [
            'transfers' => $transfers,
            'filters'   => $request->only(['q', 'status']),
        ]);
    }

    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/StockTransfers/Create', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id'   => 'required|exists:warehouses,id',
            'date'              => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'       => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $transfer = StockTransfer::create([
                'code'              => StockTransfer::generateCode(),
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id'   => $request->to_warehouse_id,
                'user_id'           => auth()->id(),
                'date'              => $request->date,
                'notes'             => $request->notes,
                'status'            => 'draft',
            ]);

            foreach ($request->items as $item) {
                $transfer->details()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                ]);
            }
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Mutasi stok disimpan sebagai draft.');
    }

    public function show($id)
    {
        $transfer = StockTransfer::with([
            'fromWarehouse', 'toWarehouse', 'user', 'details.product'
        ])->findOrFail($id);

        return Inertia::render('Dashboard/StockTransfers/Show', [
            'transfer' => $transfer,
        ]);
    }

    public function edit($id)
    {
        $transfer = StockTransfer::with(['details.product'])->findOrFail($id);

        if ($transfer->status === 'finalized') {
            return redirect()->route('stock-transfers.index')
                ->with('error', 'Mutasi yang sudah difinalisasi tidak dapat diedit.');
        }

        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/StockTransfers/Edit', [
            'transfer'   => $transfer,
            'warehouses' => $warehouses,
        ]);
    }

    public function update(Request $request, $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status === 'finalized') {
            return redirect()->route('stock-transfers.index')
                ->with('error', 'Tidak dapat mengedit mutasi yang sudah difinalisasi.');
        }

        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id'   => 'required|exists:warehouses,id',
            'date'              => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'       => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $transfer) {
            $transfer->update([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id'   => $request->to_warehouse_id,
                'date'              => $request->date,
                'notes'             => $request->notes,
            ]);

            $transfer->details()->delete();

            foreach ($request->items as $item) {
                $transfer->details()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                ]);
            }
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Mutasi stok berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status === 'finalized') {
            return redirect()->route('stock-transfers.index')
                ->with('error', 'Tidak dapat menghapus mutasi yang sudah difinalisasi.');
        }

        $transfer->details()->delete();
        $transfer->delete();

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Mutasi stok berhasil dihapus.');
    }

    /**
     * Finalize: kurangi stok gudang asal, tambah stok gudang tujuan,
     * catat mutasi ProductStock kedua sisi, buat jurnal informatif.
     */
    public function finalize($id)
    {
        $transfer = StockTransfer::with(['details', 'fromWarehouse', 'toWarehouse'])->findOrFail($id);

        if ($transfer->status === 'finalized') {
            return redirect()->route('stock-transfers.index')
                ->with('error', 'Mutasi sudah pernah difinalisasi.');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->details as $detail) {
                // === Gudang Asal: Kurangi Stok ===
                $from = ProductWarehouse::firstOrCreate(
                    ['product_id' => $detail->product_id, 'warehouse_id' => $transfer->from_warehouse_id],
                    ['stock' => 0]
                );
                $prevFrom        = $from->stock;
                $from->stock    -= $detail->qty;
                $from->save();

                ProductStock::create([
                    'product_id'       => $detail->product_id,
                    'warehouse_id'     => $transfer->from_warehouse_id,
                    'type'             => 'out',
                    'qty'              => $detail->qty,
                    'previous_stock'   => $prevFrom,
                    'current_stock'    => $from->stock,
                    'user_id'          => auth()->id(),
                    'note'             => "Mutasi keluar ke {$transfer->toWarehouse->name} [{$transfer->code}]",
                ]);

                // === Gudang Tujuan: Tambah Stok ===
                $to = ProductWarehouse::firstOrCreate(
                    ['product_id' => $detail->product_id, 'warehouse_id' => $transfer->to_warehouse_id],
                    ['stock' => 0]
                );
                $prevTo        = $to->stock;
                $to->stock    += $detail->qty;
                $to->save();

                ProductStock::create([
                    'product_id'       => $detail->product_id,
                    'warehouse_id'     => $transfer->to_warehouse_id,
                    'type'             => 'in',
                    'qty'              => $detail->qty,
                    'previous_stock'   => $prevTo,
                    'current_stock'    => $to->stock,
                    'user_id'          => auth()->id(),
                    'note'             => "Mutasi masuk dari {$transfer->fromWarehouse->name} [{$transfer->code}]",
                ]);
            }

            // === Jurnal Akuntansi ===
            $this->createTransferJournal($transfer);

            $transfer->update([
                'status'       => 'finalized',
                'finalized_at' => now(),
            ]);
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Mutasi stok berhasil difinalisasi.');
    }

    /**
     * Jurnal Mutasi Stok Antar Gudang:
     *
     * Secara akuntansi, transfer internal tidak mengubah TOTAL nilai persediaan
     * (barang tetap milik perusahaan). Namun jurnal tetap dibuat sebagai
     * catatan/audit trail resmi dengan menggunakan akun Transit Persediaan:
     *
     *   Dr. Persediaan – Gudang Tujuan (inventory account)
     *   Cr. Persediaan – Gudang Asal   (inventory account)
     *
     * Jika kedua sisi menggunakan akun inventory yang sama, net = 0,
     * namun jurnal tetap berfungsi sebagai dokumentasi perpindahan aset internal.
     */
    private function createTransferJournal(StockTransfer $transfer)
    {
        $inventoryAccountId = AccountSetting::getAccountId('inventory');

        if (!$inventoryAccountId) return;

        // Hitung total nilai berdasarkan harga beli produk
        $totalValue = $transfer->details->reduce(function ($carry, $detail) {
            $buyPrice = $detail->product?->buy_price ?? 0;
            return $carry + ($buyPrice * $detail->qty);
        }, 0);

        if ($totalValue <= 0) return;

        $journal = Journal::create([
            'date'        => \Carbon\Carbon::parse($transfer->date)->format('Y-m-d'),
            'reference'   => Journal::generateReference('MT'),
            'description' => "Mutasi Stok {$transfer->code}: {$transfer->fromWarehouse->name} → {$transfer->toWarehouse->name}",
            'source_type' => StockTransfer::class,
            'source_id'   => $transfer->id,
            'user_id'     => auth()->id(),
        ]);

        // Debit: Persediaan Gudang Tujuan
        JournalEntry::create([
            'journal_id'  => $journal->id,
            'account_id'  => $inventoryAccountId,
            'debit'       => $totalValue,
            'credit'      => 0,
            'description' => "Persediaan masuk – {$transfer->toWarehouse->name}",
        ]);

        // Credit: Persediaan Gudang Asal
        JournalEntry::create([
            'journal_id'  => $journal->id,
            'account_id'  => $inventoryAccountId,
            'debit'       => 0,
            'credit'      => $totalValue,
            'description' => "Persediaan keluar – {$transfer->fromWarehouse->name}",
        ]);

        $journal->recalculateTotals();
    }

    /**
     * Cari produk beserta stok di gudang tertentu.
     */
    public function searchProduct(Request $request)
    {
        $warehouseId = $request->warehouse_id;

        $products = Product::where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('barcode', 'like', '%' . $request->q . '%');
            })
            ->limit(10)
            ->get()
            ->map(function ($product) use ($warehouseId) {
                $stock = 0;
                if ($warehouseId) {
                    $pw = ProductWarehouse::where('product_id', $product->id)
                        ->where('warehouse_id', $warehouseId)
                        ->first();
                    $stock = $pw?->stock ?? 0;
                }

                return [
                    'id'        => $product->id,
                    'title'     => $product->title,
                    'barcode'   => $product->barcode,
                    'buy_price' => $product->buy_price,
                    'image'     => $product->image,
                    'stock'     => $stock,
                ];
            });

        return response()->json($products);
    }
}
