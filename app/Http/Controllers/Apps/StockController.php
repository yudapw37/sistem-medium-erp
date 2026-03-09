<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldStockAwal;
use App\Models\OldStockRunning;
use App\Models\OldBarang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $stockAwal = OldStockAwal::with('barang')->latest()->paginate(10);

        // Get available barang that have been used in purchases
        $barangs = OldBarang::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('old_purchase_details')
                ->whereRaw('old_purchase_details.code_barang = old_ms_barang.id');
        })->orderBy('judul_buku')->get();

        return Inertia::render('Dashboard/Stock/Index', [
            'stockAwal' => $stockAwal,
            'barangs' => $barangs,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code_barang' => 'required|string|exists:old_ms_barang,id',
            'qty' => 'required|integer|min:0',
            'tanggal' => 'required|date',
        ]);

        DB::transaction(function () use ($request) {
            $stockAwal = OldStockAwal::updateOrCreate(
                ['code_barang' => $request->code_barang],
                [
                    'qty' => $request->qty,
                    'tanggal' => $request->tanggal,
                ]
            );

            // Update stock running
            $stockRunning = OldStockRunning::firstOrNew(['code_barang' => $request->code_barang]);
            $stockRunning->stock_awal = $request->qty;
            $stockRunning->stock_saldo = $stockRunning->stock_awal + $stockRunning->stock_masuk - $stockRunning->stock_keluar;
            $stockRunning->save();
        });

        return redirect()->back()->with('success', 'Stock awal berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'qty' => 'required|integer|min:0',
            'tanggal' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $id) {
            $stockAwal = OldStockAwal::findOrFail($id);

            $stockAwal->update([
                'qty' => $request->qty,
                'tanggal' => $request->tanggal,
            ]);

            // Update stock running
            $stockRunning = OldStockRunning::where('code_barang', $stockAwal->code_barang)->first();
            if ($stockRunning) {
                $stockRunning->stock_awal = $request->qty;
                $stockRunning->stock_saldo = $stockRunning->stock_awal + $stockRunning->stock_masuk - $stockRunning->stock_keluar;
                $stockRunning->save();
            }
        });

        return redirect()->back()->with('success', 'Stock awal berhasil diperbarui');
    }

    public function destroy($id)
    {
        $stockAwal = OldStockAwal::findOrFail($id);
        $codeBarang = $stockAwal->code_barang;

        $stockAwal->delete();

        // Update stock running to set stock_awal to 0
        $stockRunning = OldStockRunning::where('code_barang', $codeBarang)->first();
        if ($stockRunning) {
            $stockRunning->stock_awal = 0;
            $stockRunning->stock_saldo = $stockRunning->stock_awal + $stockRunning->stock_masuk - $stockRunning->stock_keluar;
            $stockRunning->save();
        }

        return redirect()->back()->with('success', 'Stock awal berhasil dihapus');
    }

    public function running()
    {
        $stockRunning = OldStockRunning::with('barang')
            ->when(request()->q, function ($query) {
                $query->where('code_barang', 'like', '%' . request()->q . '%')
                    ->orWhereHas('barang', function ($q) {
                        $q->where('judul_buku', 'like', '%' . request()->q . '%');
                    });
            })
            ->orderBy('code_barang')
            ->paginate(10);

        return Inertia::render('Dashboard/Stock/Running', [
            'stockRunning' => $stockRunning
        ]);
    }
}
