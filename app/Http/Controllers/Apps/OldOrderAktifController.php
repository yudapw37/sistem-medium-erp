<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldOrderAktif;
use App\Models\OldOrderAktifDetail;
use App\Models\OldOrder;
use App\Models\OldOrderDetail;
use App\Models\OldStockRunning;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class OldOrderAktifController extends Controller
{
    /**
     * Resume: monthly summary of old_order_aktif with semester pagination.
     */
    public function resume(Request $request)
    {
        // Available years from old_order (so all years show, not just synced ones)
        $availableYears = DB::table('old_order')
            ->selectRaw('DISTINCT YEAR(created_at) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Default to current year if no data
        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }

        $year = (int) ($request->year ?? ($availableYears[0] ?? date('Y')));
        $semester = (int) ($request->semester ?? (date('n') <= 6 ? 1 : 2));

        $startMonth = $semester === 1 ? 1 : 7;
        $endMonth = $semester === 1 ? 6 : 12;

        $months = DB::table('old_order_aktif')
            ->join('old_order', 'old_order_aktif.old_order_id', '=', 'old_order.id')
            ->select(
                DB::raw('YEAR(old_order.created_at) as year'),
                DB::raw('MONTH(old_order.created_at) as month'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(old_order_aktif.total_barang) as total_barang'),
                DB::raw('SUM(old_order_aktif.total_harga + COALESCE(old_order_aktif.biaya_expedisi, 0) - COALESCE(old_order_aktif.total_diskon, 0) - COALESCE(old_order_aktif.diskon_kode_unik, 0)) as total_nominal'),
                // Final counts
                DB::raw('SUM(CASE WHEN old_order_aktif.is_final = 1 THEN 1 ELSE 0 END) as final_orders'),
                DB::raw('SUM(CASE WHEN old_order_aktif.is_final = 0 THEN 1 ELSE 0 END) as unfinal_orders'),
                DB::raw('SUM(CASE WHEN old_order_aktif.is_final = 1 THEN old_order_aktif.total_barang ELSE 0 END) as final_barang'),
                DB::raw('SUM(CASE WHEN old_order_aktif.is_final = 1 THEN (old_order_aktif.total_harga + COALESCE(old_order_aktif.biaya_expedisi, 0) - COALESCE(old_order_aktif.total_diskon, 0) - COALESCE(old_order_aktif.diskon_kode_unik, 0)) ELSE 0 END) as final_nominal')
            )
            ->whereYear('old_order.created_at', $year)
            ->whereRaw('MONTH(old_order.created_at) BETWEEN ? AND ?', [$startMonth, $endMonth])
            ->groupBy(DB::raw('YEAR(old_order.created_at)'), DB::raw('MONTH(old_order.created_at)'))
            ->orderBy('month', 'desc')
            ->get();

        return Inertia::render('Dashboard/OldOrdersAktif/Resume', [
            'months' => $months,
            'availableYears' => $availableYears,
            'filters' => [
                'year' => $year,
                'semester' => $semester,
            ],
        ]);
    }

    /**
     * Detail: list old_order_aktif for a specific month.
     */
    public function resumeDetail($year, $month)
    {
        $orders = OldOrderAktif::with(['details.barang'])
            ->select('old_order_aktif.*', 'old_order.created_at as order_date')
            ->join('old_order', 'old_order_aktif.old_order_id', '=', 'old_order.id')
            ->whereYear('old_order.created_at', $year)
            ->whereMonth('old_order.created_at', $month)
            ->get()
            ->map(function ($aktif) {
                // Add computed total nominal
                $aktif->computed_nominal = $aktif->total_harga + ($aktif->biaya_expedisi ?? 0) - ($aktif->total_diskon ?? 0) - ($aktif->diskon_kode_unik ?? 0);
                
                // Map item names for details
                foreach ($aktif->details as $detail) {
                    $detail->nama_barang = $detail->barang->judul_buku ?? '-';
                }
                
                return $aktif;
            });

        return response()->json($orders);
    }

    /**
     * Sync stock for all unfinal orders in a specific month.
     * Marks them as is_final = true and updates OldStockRunning.
     */
    public function syncStock($year, $month)
    {
        $finalized = 0;
        $alreadyFinal = 0;

        DB::transaction(function () use ($year, $month, &$finalized, &$alreadyFinal) {
            $orders = OldOrderAktif::with('details')
                ->whereHas('oldOrder', function ($q) use ($year, $month) {
                    $q->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
                })
                ->get();

            foreach ($orders as $aktif) {
                if ($aktif->is_final) {
                    $alreadyFinal++;
                    continue;
                }

                $aktif->update([
                    'is_final' => true,
                    'final_at' => now(),
                ]);

                // Update stock running (pengurangan)
                foreach ($aktif->details as $detail) {
                    if (!$detail->code_barang) continue;

                    $stockRunning = OldStockRunning::firstOrNew(['code_barang' => $detail->code_barang]);
                    $stockRunning->stock_keluar = ($stockRunning->stock_keluar ?? 0) + $detail->jumlah;
                    $stockRunning->stock_saldo = ($stockRunning->stock_awal ?? 0) + ($stockRunning->stock_masuk ?? 0) - $stockRunning->stock_keluar;
                    $stockRunning->save();
                }

                $finalized++;
            }
        });

        // Return updated summary
        $summary = DB::table('old_order_aktif')
            ->join('old_order', 'old_order_aktif.old_order_id', '=', 'old_order.id')
            ->whereYear('old_order.created_at', $year)
            ->whereMonth('old_order.created_at', $month)
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN old_order_aktif.is_final = 1 THEN 1 ELSE 0 END) as final_orders,
                SUM(CASE WHEN old_order_aktif.is_final = 0 THEN 1 ELSE 0 END) as unfinal_orders
            ')
            ->first();

        return response()->json([
            'finalized' => $finalized,
            'already_final' => $alreadyFinal,
            'final_orders' => (int) ($summary->final_orders ?? 0),
            'unfinal_orders' => (int) ($summary->unfinal_orders ?? 0),
        ]);
    }

    /**
     * Unfinal Stock: reverse stock_keluar and set is_final = false.
     */
    public function unfinalStock($year, $month)
    {
        $unfinalized = 0;
        $alreadyUnfinal = 0;

        DB::transaction(function () use ($year, $month, &$unfinalized, &$alreadyUnfinal) {
            $orders = OldOrderAktif::with('details')
                ->whereHas('oldOrder', function ($q) use ($year, $month) {
                    $q->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
                })
                ->get();

            foreach ($orders as $aktif) {
                if (!$aktif->is_final) {
                    $alreadyUnfinal++;
                    continue;
                }

                // Reverse stock running
                foreach ($aktif->details as $detail) {
                    if (!$detail->code_barang) continue;

                    $stockRunning = OldStockRunning::where('code_barang', $detail->code_barang)->first();
                    if ($stockRunning) {
                        $stockRunning->stock_keluar = max(0, ($stockRunning->stock_keluar ?? 0) - $detail->jumlah);
                        $stockRunning->stock_saldo = ($stockRunning->stock_awal ?? 0) + ($stockRunning->stock_masuk ?? 0) - $stockRunning->stock_keluar;
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
     * Original index (kept for compatibility).
     */
    public function index()
    {
        $oldOrderAktif = OldOrderAktif::when(request()->q, function ($query) {
            $query->where('nama_penerima', 'like', '%' . request()->q . '%')
                ->orWhere('code_customer', 'like', '%' . request()->q . '%');
        })->with('details')->latest()->paginate(10);

        return Inertia::render('Dashboard/OldOrdersAktif/Index', [
            'oldOrderAktif' => $oldOrderAktif
        ]);
    }

    public function getUnfinalOrders()
    {
        $orders = OldOrder::where('resume_status', true)
            ->whereDoesntHave('aktif')
            ->with('details')
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'required|exists:old_order,id',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->order_ids as $orderId) {
                $order = OldOrder::with('details')->find($orderId);

                if (!$order) continue;

                if (OldOrderAktif::where('old_order_id', $orderId)->exists()) {
                    continue;
                }

                $aktif = OldOrderAktif::create([
                    'old_order_id' => $order->id,
                    'code_customer' => $order->code_customer,
                    'nama_pengirim' => $order->nama_pengirim,
                    'telephone_pengirim' => $order->telephone_pengirim,
                    'nama_penerima' => $order->nama_penerima,
                    'telephone_penerima' => $order->telephone_penerima,
                    'alamat' => $order->alamat,
                    'kecamatan' => $order->kecamatan,
                    'kab_kota' => $order->kab_kota,
                    'total_barang' => $order->total_barang,
                    'total_harga' => $order->total_harga,
                    'total_diskon' => $order->total_diskon,
                    'diskon_kode_unik' => $order->diskonKodeUnik,
                    'biaya_expedisi' => $order->biayaExpedisi,
                    'is_final' => false,
                ]);

                foreach ($order->details as $detail) {
                    OldOrderAktifDetail::create([
                        'old_order_aktif_id' => $aktif->id,
                        'code_order' => $detail->code_order,
                        'code_barang' => $detail->code_barang,
                        'nama_promo' => $detail->nama_promo,
                        'jumlah' => $detail->jumlah,
                        'harga' => $detail->harga,
                        'harga_promo' => $detail->harga_promo,
                        'diskon' => $detail->diskon,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Order berhasil dipindahkan ke aktif');
    }

    public function destroy($id)
    {
        $aktif = OldOrderAktif::findOrFail($id);

        // If already final, rollback stock first
        if ($aktif->is_final) {
            foreach ($aktif->details as $detail) {
                if (!$detail->code_barang) continue;

                $stockRunning = OldStockRunning::where('code_barang', $detail->code_barang)->first();
                if ($stockRunning) {
                    $stockRunning->stock_keluar = max(0, $stockRunning->stock_keluar - $detail->jumlah);
                    $stockRunning->stock_saldo = ($stockRunning->stock_awal ?? 0) + ($stockRunning->stock_masuk ?? 0) - $stockRunning->stock_keluar;
                    $stockRunning->save();
                }
            }
        }

        $aktif->delete();

        return redirect()->back()->with('success', 'Order aktif berhasil dihapus');
    }
}
