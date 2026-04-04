<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldStockAwal;
use App\Models\OldStockRunning;
use App\Models\OldBarang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class StockController extends Controller
{
    public function index()
    {
        $stockAwal = OldStockAwal::with('barang')
            ->when(request()->q, function ($query) {
                $query->where('code_barang', 'like', '%' . request()->q . '%')
                    ->orWhereHas('barang', function ($q) {
                        $q->where('judul_buku', 'like', '%' . request()->q . '%');
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Get all barang for selection
        $barangs = OldBarang::orderBy('judul_buku')->get();

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
                    'is_synced' => false,
                ]
            );
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
                'is_synced' => false,
            ]);
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
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/Stock/Running', [
            'stockRunning' => $stockRunning
        ]);
    }

    public function downloadTemplate()
    {
        $header = [
            ['KodeBuku', 'StockScan'],
            ['ContohKode1', 10],
            ['ContohKode1', 5],
            ['ContohKode2', 20],
        ];

        $xlsx = SimpleXLSXGen::fromArray($header);
        
        return response()->streamDownload(function() use ($xlsx) {
            echo $xlsx;
        }, 'template_import_stock_awal.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        
        if ($xlsx = SimpleXLSX::parse($file->getPathname())) {
            $rows = $xlsx->rows();
            $header = array_shift($rows);
            
            // Map header to indices
            $kodeBukuIdx = array_search('KodeBuku', $header);
            $stockScanIdx = array_search('StockScan', $header);

            if ($kodeBukuIdx === false || $stockScanIdx === false) {
                return redirect()->back()->with('error', 'Format Excel salah. Pastikan kolom KodeBuku dan StockScan ada.');
            }

            $aggregatedData = [];
            foreach ($rows as $row) {
                $code = trim($row[$kodeBukuIdx] ?? '');
                $qty = (int) ($row[$stockScanIdx] ?? 0);

                if (empty($code)) continue;

                if (!isset($aggregatedData[$code])) {
                    $aggregatedData[$code] = 0;
                }
                $aggregatedData[$code] += $qty;
            }

            if (empty($aggregatedData)) {
                return redirect()->back()->with('error', 'Tidak ada data valid untuk diimport.');
            }

            $tanggal = '2021-12-01';

            DB::transaction(function () use ($aggregatedData, $tanggal) {
                foreach ($aggregatedData as $code => $qty) {
                    // Update or Create Stock Awal
                    OldStockAwal::updateOrCreate(
                        ['code_barang' => $code],
                        [
                            'qty' => $qty,
                            'tanggal' => $tanggal,
                            'is_synced' => false,
                        ]
                    );
                }
            });

            return redirect()->back()->with('success', 'Berhasil mengimport ' . count($aggregatedData) . ' data stock awal.');
        } else {
            return redirect()->back()->with('error', SimpleXLSX::parseError());
        }
    }

    public function sync()
    {
        $unSynced = OldStockAwal::where('is_synced', false)->get();

        if ($unSynced->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data yang perlu disinkronisasi.');
        }

        DB::transaction(function () use ($unSynced) {
            foreach ($unSynced as $item) {
                // Update stock running
                $stockRunning = OldStockRunning::firstOrNew(['code_barang' => $item->code_barang]);
                $stockRunning->stock_awal = $item->qty;
                $stockRunning->stock_saldo = $stockRunning->stock_awal + $stockRunning->stock_masuk - $stockRunning->stock_keluar;
                $stockRunning->save();

                // Mark as synced
                $item->update([
                    'is_synced' => true,
                    'synced_at' => now(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Berhasil menyinkronkan ' . $unSynced->count() . ' data ke Stock Running.');
    }
}
