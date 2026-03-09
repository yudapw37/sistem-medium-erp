<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldPurchaseAktif;
use App\Models\OldPurchaseAktifDetail;
use App\Models\OldPurchase;
use App\Models\OldPurchaseDetail;
use App\Models\OldStockRunning;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class OldPurchaseAktifController extends Controller
{
    /**
     * Resume: monthly summary of purchase aktif, with semester pagination.
     */
    public function resume(Request $request)
    {
        // Available years from old_purchases
        $availableYears = DB::table('old_purchases')
            ->selectRaw('DISTINCT YEAR(tanggal_faktur) as year')
            ->whereNotNull('tanggal_faktur')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');
        $currentSemester = $currentMonth <= 6 ? 1 : 2;

        $year = (int) ($request->year ?? $currentYear);
        $semester = (int) ($request->semester ?? $currentSemester);

        $startMonth = $semester === 1 ? 1 : 7;
        $endMonth = $semester === 1 ? 6 : 12;

        $months = collect();
        for ($m = $startMonth; $m <= $endMonth; $m++) {
            $summary = DB::table('old_purchase_aktif')
                ->join('old_purchases', 'old_purchase_aktif.old_purchase_id', '=', 'old_purchases.id')
                ->whereYear('old_purchases.tanggal_faktur', $year)
                ->whereMonth('old_purchases.tanggal_faktur', $m)
                ->selectRaw('
                    COUNT(*) as total_purchases,
                    SUM(old_purchase_aktif.harga_total) as total_nominal,
                    SUM(CASE WHEN old_purchase_aktif.is_final = 1 THEN 1 ELSE 0 END) as final_purchases,
                    SUM(CASE WHEN old_purchase_aktif.is_final = 0 THEN 1 ELSE 0 END) as unfinal_purchases,
                    SUM(CASE WHEN old_purchase_aktif.is_final = 1 THEN old_purchase_aktif.harga_total ELSE 0 END) as final_nominal
                ')
                ->first();

            $months->push([
                'year' => $year,
                'month' => $m,
                'total_purchases' => (int) ($summary->total_purchases ?? 0),
                'total_nominal' => (float) ($summary->total_nominal ?? 0),
                'final_purchases' => (int) ($summary->final_purchases ?? 0),
                'unfinal_purchases' => (int) ($summary->unfinal_purchases ?? 0),
                'final_nominal' => (float) ($summary->final_nominal ?? 0),
            ]);
        }

        return Inertia::render('Dashboard/OldPurchasesAktif/Resume', [
            'months' => $months,
            'availableYears' => $availableYears,
            'filters' => [
                'year' => $year,
                'semester' => $semester,
            ],
        ]);
    }

    /**
     * Detail: list purchase aktif for a specific month.
     */
    public function resumeDetail($year, $month)
    {
        $purchases = OldPurchaseAktif::select('old_purchase_aktif.*', 'old_purchases.tanggal_faktur as purchase_date')
            ->join('old_purchases', 'old_purchase_aktif.old_purchase_id', '=', 'old_purchases.id')
            ->whereYear('old_purchases.tanggal_faktur', $year)
            ->whereMonth('old_purchases.tanggal_faktur', $month)
            ->orderBy('old_purchases.tanggal_faktur', 'desc')
            ->get();

        return response()->json($purchases);
    }

    /**
     * Sync Stock: finalize unfinal purchases and add stock_masuk to stock_running.
     */
    public function syncStock($year, $month)
    {
        $finalized = 0;
        $alreadyFinal = 0;

        DB::transaction(function () use ($year, $month, &$finalized, &$alreadyFinal) {
            $purchases = OldPurchaseAktif::with('details')
                ->whereHas('oldPurchase', function ($q) use ($year, $month) {
                    $q->whereYear('tanggal_faktur', $year)
                      ->whereMonth('tanggal_faktur', $month);
                })
                ->get();

            foreach ($purchases as $aktif) {
                if ($aktif->is_final) {
                    $alreadyFinal++;
                    continue;
                }

                // Mark as final
                $aktif->update([
                    'is_final' => true,
                    'final_at' => now(),
                ]);

                // Update stock running
                foreach ($aktif->details as $detail) {
                    if (!$detail->code_barang) continue;

                    $stockRunning = OldStockRunning::firstOrNew(['code_barang' => $detail->code_barang]);
                    $stockRunning->stock_masuk = ($stockRunning->stock_masuk ?? 0) + $detail->qty;
                    $stockRunning->stock_saldo = ($stockRunning->stock_awal ?? 0) + $stockRunning->stock_masuk - ($stockRunning->stock_keluar ?? 0);
                    $stockRunning->save();
                }

                $finalized++;
            }
        });

        return response()->json([
            'finalized' => $finalized,
            'already_final' => $alreadyFinal,
        ]);
    }

    /**
     * Unfinal Stock: reverse stock_masuk and set is_final = false.
     */
    public function unfinalStock($year, $month)
    {
        $unfinalized = 0;
        $alreadyUnfinal = 0;

        DB::transaction(function () use ($year, $month, &$unfinalized, &$alreadyUnfinal) {
            $purchases = OldPurchaseAktif::with('details')
                ->whereHas('oldPurchase', function ($q) use ($year, $month) {
                    $q->whereYear('tanggal_faktur', $year)
                      ->whereMonth('tanggal_faktur', $month);
                })
                ->get();

            foreach ($purchases as $aktif) {
                if (!$aktif->is_final) {
                    $alreadyUnfinal++;
                    continue;
                }

                // Reverse stock running
                foreach ($aktif->details as $detail) {
                    if (!$detail->code_barang) continue;

                    $stockRunning = OldStockRunning::where('code_barang', $detail->code_barang)->first();
                    if ($stockRunning) {
                        $stockRunning->stock_masuk = max(0, ($stockRunning->stock_masuk ?? 0) - $detail->qty);
                        $stockRunning->stock_saldo = ($stockRunning->stock_awal ?? 0) + $stockRunning->stock_masuk - ($stockRunning->stock_keluar ?? 0);
                        $stockRunning->save();
                    }
                }

                // Mark as unfinal
                $aktif->update([
                    'is_final' => false,
                    'final_at' => null,
                ]);

                $unfinalized++;
            }
        });

        return response()->json([
            'unfinalized' => $unfinalized,
            'already_unfinal' => $alreadyUnfinal,
        ]);
    }

    /**
     * Map barang codes on purchase details.
     */
    public function mapBarang(Request $request)
    {
        $request->validate([
            'details' => 'required|array',
            'details.*.id' => 'required|exists:old_purchase_details,id',
            'details.*.code_barang' => 'required|string',
        ]);

        foreach ($request->details as $detail) {
            OldPurchaseDetail::where('id', $detail['id'])->update([
                'code_barang' => $detail['code_barang']
            ]);
        }

        return redirect()->back()->with('success', 'Mapping barang berhasil disimpan');
    }
}
