<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldOrder;
use App\Models\OldOrderAktif;
use App\Models\OldOrderAktifDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class OldOrderController extends Controller
{
    /**
     * Resume: monthly summary of old orders.
     */
    public function resume(Request $request)
    {
        // Available years
        $availableYears = DB::table('old_order')
            ->selectRaw('DISTINCT YEAR(created_at) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        $year = (int) ($request->year ?? date('Y'));
        $semester = (int) ($request->semester ?? (date('n') <= 6 ? 1 : 2));

        // Semester 1 = Jan-Jun, Semester 2 = Jul-Dec
        $startMonth = $semester === 1 ? 1 : 7;
        $endMonth = $semester === 1 ? 6 : 12;

        $months = DB::table('old_order')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) as total_nominal'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN 1 ELSE 0 END) as true_orders'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN total_barang ELSE 0 END) as true_barang'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN (total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) ELSE 0 END) as true_nominal')
            )
            ->whereYear('created_at', $year)
            ->whereRaw('MONTH(created_at) BETWEEN ? AND ?', [$startMonth, $endMonth])
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('month', 'desc')
            ->get();

        // Check sync status per month (count synced & final)
        foreach ($months as $m) {
            $syncInfo = DB::table('old_order_aktif')
                ->join('old_order', 'old_order_aktif.old_order_id', '=', 'old_order.id')
                ->whereYear('old_order.created_at', $m->year)
                ->whereMonth('old_order.created_at', $m->month)
                ->selectRaw('COUNT(*) as synced_count, SUM(CASE WHEN old_order_aktif.is_final = 1 THEN 1 ELSE 0 END) as final_count')
                ->first();
            $m->synced_count = (int) ($syncInfo->synced_count ?? 0);
            $m->final_count = (int) ($syncInfo->final_count ?? 0);
        }

        return Inertia::render('Dashboard/OldOrders/Resume', [
            'months' => $months,
            'availableYears' => $availableYears,
            'filters' => [
                'year' => $year,
                'semester' => $semester,
            ],
        ]);
    }

    /**
     * Resume Report: aggregated book sales.
     */
    public function resumeReport(Request $request)
    {
        $month = $request->month ?? date('n');
        $year = $request->year ?? date('Y');
        $resumeStatus = $request->has('resume_status') ? $request->boolean('resume_status') : true;

        // Use date range for better performance (utilizing created_at index)
        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $items = DB::table('old_orderdetail')
            ->join('old_order', 'old_orderdetail.code_order', '=', 'old_order.id')
            ->join('old_ms_barang', 'old_orderdetail.code_barang', '=', 'old_ms_barang.id')
            ->select(
                'old_ms_barang.judul_buku as nama_buku',
                DB::raw('SUM(old_orderdetail.jumlah) as jumlah_buku'),
                DB::raw('SUM(
                    (CASE WHEN old_orderdetail.harga_promo > 0 THEN old_orderdetail.harga_promo ELSE old_orderdetail.Harga END) 
                    * old_orderdetail.jumlah 
                    * (1 - COALESCE(old_orderdetail.diskon, 0) / 100)
                ) as total_harga_buku')
            )
            ->whereBetween('old_order.created_at', [$startDate, $endDate])
            ->where('old_order.resume_status', $resumeStatus)
            ->groupBy('old_ms_barang.judul_buku')
            ->orderBy('jumlah_buku', 'desc')
            ->get();

        return Inertia::render('Dashboard/OldOrders/ResumeReport', [
            'items' => $items,
            'filters' => [
                'month' => (int) $month,
                'year' => (int) $year,
                'resume_status' => $resumeStatus,
            ],
        ]);
    }

    /**
     * Resume Report Detail: list orders for a specific book title.
     */
    public function resumeReportDetail(Request $request)
    {
        $request->validate([
            'nama_buku' => 'required|string',
        ]);

        $namaBuku = $request->nama_buku;
        $month = $request->month ?? date('n');
        $year = $request->year ?? date('Y');
        $resumeStatus = $request->has('resume_status') ? $request->boolean('resume_status') : true;

        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $orders = DB::table('old_orderdetail')
            ->join('old_order', 'old_orderdetail.code_order', '=', 'old_order.id')
            ->join('old_ms_barang', 'old_orderdetail.code_barang', '=', 'old_ms_barang.id')
            ->leftJoin('old_ms_customer', 'old_order.code_customer', '=', 'old_ms_customer.id')
            ->select(
                'old_order.id as order_id',
                'old_order.created_at',
                'old_ms_customer.nama as nama_customer',
                'old_orderdetail.jumlah',
                DB::raw('(CASE WHEN old_orderdetail.harga_promo > 0 THEN old_orderdetail.harga_promo ELSE old_orderdetail.Harga END) as harga_satuan'),
                DB::raw('(CASE WHEN old_orderdetail.harga_promo > 0 THEN old_orderdetail.harga_promo ELSE old_orderdetail.Harga END) * old_orderdetail.jumlah as subtotal')
            )
            ->where('old_ms_barang.judul_buku', $namaBuku)
            ->whereBetween('old_order.created_at', [$startDate, $endDate])
            ->where('old_order.resume_status', $resumeStatus)
            ->orderBy('old_order.created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Resume detail: list orders for a specific month.
     */
    public function resumeDetail($year, $month)
    {
        $orders = OldOrder::with('customer')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $order->computed_nominal = $order->total_harga + ($order->biayaExpedisi ?? 0) - ($order->totalDiskon ?? 0) - ($order->diskonKodeUnik ?? 0);
                return $order;
            });

        return response()->json($orders);
    }

    /**
     * Toggle resume_status for a single old order.
     */
    public function toggleResumeStatus(Request $request, $id)
    {
        $order = OldOrder::findOrFail($id);
        $order->resume_status = $request->boolean('resume_status');
        $order->save();

        // Return updated monthly summary for the order's month
        $year = $order->created_at->year;
        $month = $order->created_at->month;

        $summary = DB::table('old_order')
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) as total_nominal'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN 1 ELSE 0 END) as true_orders'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN total_barang ELSE 0 END) as true_barang'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN (total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) ELSE 0 END) as true_nominal')
            )
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->first();

        return response()->json([
            'order' => $order,
            'summary' => $summary,
        ]);
    }

    /**
     * Bulk toggle resume_status for all orders in a specific month.
     */
    public function bulkToggleResumeStatus(Request $request, $year, $month)
    {
        $newStatus = $request->boolean('resume_status');

        $updated = OldOrder::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->update(['resume_status' => $newStatus]);

        $summary = DB::table('old_order')
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) as total_nominal'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN 1 ELSE 0 END) as true_orders'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN total_barang ELSE 0 END) as true_barang'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN (total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) ELSE 0 END) as true_nominal')
            )
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->first();

        return response()->json([
            'updated' => $updated,
            'summary' => $summary,
        ]);
    }

    /**
     * Apply suggestion for resume_status based on a target nominal.
     */
    public function applyActivationSuggestion(Request $request, $year, $month)
    {
        $request->validate([
            'target_nominal' => 'required|numeric|min:0',
        ]);

        $targetNominal = (float) $request->target_nominal;

        DB::transaction(function () use ($year, $month, $targetNominal) {
            // 1. Reset all orders for the month to resume_status = 0
            OldOrder::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->update(['resume_status' => 0]);

            // 2. Identify active days (days that actually have orders)
            $activeDates = OldOrder::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->selectRaw('DATE(created_at) as date')
                ->groupBy('date')
                ->pluck('date');

            if ($activeDates->isEmpty()) {
                return;
            }

            $targetPerDay = $targetNominal / $activeDates->count();
            $idsToActivate = [];

            // 3. Process each day and pick smallest orders first
            foreach ($activeDates as $date) {
                $orders = OldOrder::whereDate('created_at', $date)
                    ->whereHas('details', function ($q) {
                        $q->whereIn('code_barang', function ($sub) {
                            $sub->select('code_barang')->from('old_ms_barang_purchase');
                        });
                    })
                    ->select('*', DB::raw('(total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) as computed_nominal'))
                    ->orderBy('computed_nominal', 'asc') // PRIORITAS NOMINAL KECIL
                    ->get();

                $currentDaySum = 0;
                foreach ($orders as $order) {
                    if ($currentDaySum + $order->computed_nominal <= $targetPerDay) {
                        $idsToActivate[] = $order->id;
                        $currentDaySum += $order->computed_nominal;
                    } else {
                        // Since it's sorted ASC, we might still find a smaller order that fits
                        // but actually we already picked the smallest ones. 
                        // If the current smallest doesn't fit, no larger one will.
                        // But wait, it's ASC, so if current smallest + sum > target, no larger will fit.
                        // So we can break for this day.
                        break;
                    }
                }
            }

            // 4. Bulk update the selected orders
            if (!empty($idsToActivate)) {
                OldOrder::whereIn('id', $idsToActivate)->update(['resume_status' => 1]);
            }
        });

        // Return updated list and summary
        $summary = DB::table('old_order')
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) as total_nominal'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN 1 ELSE 0 END) as true_orders'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN total_barang ELSE 0 END) as true_barang'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN (total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) ELSE 0 END) as true_nominal')
            )
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->first();

        $updatedOrders = OldOrder::with('customer')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $order->computed_nominal = $order->total_harga + ($order->biayaExpedisi ?? 0) - ($order->totalDiskon ?? 0) - ($order->diskonKodeUnik ?? 0);
                return $order;
            });

        return response()->json([
            'orders' => $updatedOrders,
            'summary' => $summary,
        ]);
    }

    /**
     * Sync active orders for a specific month to old_order_aktif.
     * Also removes non-final orders that are no longer active.
     */
    public function syncMonth($year, $month)
    {
        $added = 0;
        $removed = 0;

        DB::transaction(function () use ($year, $month, &$added, &$removed) {
            // 1. ADD: active orders not yet in old_order_aktif
            $ordersToAdd = OldOrder::with('details')
                ->where('resume_status', true)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereDoesntHave('aktif')
                ->get();

            foreach ($ordersToAdd as $order) {
                $aktif = OldOrderAktif::create([
                    'old_order_id'    => $order->id,
                    'code_customer'   => $order->code_customer,
                    'nama_pengirim'   => $order->nama_pengirim,
                    'telephone_pengirim' => $order->telephone_pengirim,
                    'nama_penerima'   => $order->nama_penerima,
                    'telephone_penerima' => $order->telephone_penerima,
                    'alamat'          => $order->alamat,
                    'kecamatan'       => $order->kecamatan,
                    'kab_kota'        => $order->kab_kota,
                    'total_barang'    => $order->total_barang,
                    'total_harga'     => $order->total_harga,
                    'total_diskon'    => $order->totalDiskon,
                    'diskon_kode_unik' => $order->diskonKodeUnik,
                    'biaya_expedisi'  => $order->biayaExpedisi,
                    'is_final'        => false,
                ]);

                foreach ($order->details as $detail) {
                    OldOrderAktifDetail::create([
                        'old_order_aktif_id' => $aktif->id,
                        'code_order'  => $detail->code_order,
                        'code_barang' => $detail->code_barang,
                        'nama_promo'  => $detail->nama_promo,
                        'jumlah'      => $detail->jumlah,
                        'harga'       => $detail->harga,
                        'harga_promo' => $detail->harga_promo,
                        'diskon'      => $detail->diskon,
                    ]);
                }

                $added++;
            }

            // 2. REMOVE: orders in old_order_aktif that are no longer active (resume_status = false)
            //    but only if NOT final (is_final = false)
            $toRemove = OldOrderAktif::where('is_final', false)
                ->whereHas('oldOrder', function ($q) use ($year, $month) {
                    $q->where('resume_status', false)
                      ->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
                })
                ->get();

            foreach ($toRemove as $aktif) {
                $aktif->details()->delete();
                $aktif->delete();
                $removed++;
            }
        });

        // Return updated sync info
        $syncInfo = DB::table('old_order_aktif')
            ->join('old_order', 'old_order_aktif.old_order_id', '=', 'old_order.id')
            ->whereYear('old_order.created_at', $year)
            ->whereMonth('old_order.created_at', $month)
            ->selectRaw('COUNT(*) as synced_count, SUM(CASE WHEN old_order_aktif.is_final = 1 THEN 1 ELSE 0 END) as final_count')
            ->first();

        return response()->json([
            'added'        => $added,
            'removed'      => $removed,
            'synced_count' => (int) ($syncInfo->synced_count ?? 0),
            'final_count'  => (int) ($syncInfo->final_count ?? 0),
        ]);
    }

    /**
     * Display a listing of old orders.
     */
    public function index(Request $request)
    {
        $orders = OldOrder::with(['customer'])
            ->when($request->q, function ($query, $q) {
                $query->where('id', 'like', '%' . $q . '%');
            })
            ->when($request->start_date, function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($request->end_date, function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Dashboard/OldOrders/Index', [
            'orders' => $orders,
            'filters' => $request->all(['q', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Display the specified old order.
     */
    public function show($id)
    {
        $order = OldOrder::with(['customer', 'details.barang'])->findOrFail($id);

        return Inertia::render('Dashboard/OldOrders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Get orders by date for bulk print selection.
     */
    public function ordersByDate(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $orders = OldOrder::with(['customer'])
            ->whereDate('created_at', $request->date)
            ->get(['id', 'code_customer', 'created_at', 'total_harga']);

        return response()->json($orders);
    }

    /**
     * Print multiple invoices for the specified old orders.
     */
    public function bulkPrint(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        $orders = OldOrder::with(['customer', 'details.barang'])
            ->whereIn('id', $request->ids)
            ->get();

        $preparedOrders = [];
        foreach ($orders as $order) {
            $preparedOrders[] = $this->prepareOrderData($order);
        }

        $logoBase64 = $this->getLogoBase64();

        $pdf = Pdf::loadView('old-orders.print', [
            'orders' => $preparedOrders,
            'logo' => $logoBase64
        ]);
        $pdf->setPaper('a4');

        return $pdf->stream('Bulk_Invoices_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Print invoice for the specified old order.
     */
    public function print($id)
    {
        $order = OldOrder::with(['customer', 'details.barang'])->findOrFail($id);
        $data = $this->prepareOrderData($order);

        $filename = 'Invoice_' . $order->id . '_' . ($order->customer->nama ?? 'Customer') . '.pdf';

        $logoBase64 = $this->getLogoBase64();

        $pdf = Pdf::loadView('old-orders.print', [
            'orders' => [$data],
            'logo' => $logoBase64
        ]);
        $pdf->setPaper('a4');

        return $pdf->stream($filename);
    }

    /**
     * Helper to get logo as base64
     */
    private function getLogoBase64()
    {
        $logoRelativePath = 'assets/logo/logoInv.png';

        // Comprehensive list of potential paths
        $paths = [
            public_path($logoRelativePath),
            realpath(base_path('../public_html/' . $logoRelativePath)),
            realpath(base_path('../public/' . $logoRelativePath)),
            realpath(base_path('public/' . $logoRelativePath)),
            isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/' . $logoRelativePath : null,
            isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/../public_html/' . $logoRelativePath : null,
        ];

        $finalPath = null;
        foreach (array_filter($paths) as $path) {
            if (file_exists($path)) {
                $finalPath = $path;
                break;
            }
        }

        if (!$finalPath) {
            // Log for debugging (temporary)
            \Log::warning("Logo not found for PDF. Tried: " . implode(', ', array_filter($paths)));
            return null;
        }

        $type = pathinfo($finalPath, PATHINFO_EXTENSION);
        $data = file_get_contents($finalPath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    /**
     * Logic to group and prepare order items (shared between single and bulk print)
     */
    private function prepareOrderData($order)
    {
        $groupedItems = [];
        $promoMap = [];

        foreach ($order->details as $detail) {
            $hasPromo = $detail->nama_promo && $detail->nama_promo !== '-'
                && $detail->code_promo && $detail->code_promo !== '-';

            if ($hasPromo) {
                $key = $detail->nama_promo;
                if (!isset($promoMap[$key])) {
                    $promoMap[$key] = [
                        'isBundle' => true,
                        'nama_promo' => $detail->nama_promo,
                        'price' => $detail->harga_promo > 0 ? $detail->harga_promo : $detail->harga,
                        'diskon' => $detail->diskon ?? 0,
                        'totalQty' => 0,
                        'totalBerat' => 0,
                        'subtotal' => 0,
                        'children' => [],
                    ];
                    $groupedItems[] = &$promoMap[$key];
                }
                $promoMap[$key]['children'][] = $detail;
                $promoMap[$key]['totalQty'] += $detail->jumlah ?? 0;
                $promoMap[$key]['totalBerat'] += floatval($detail->barang->berat ?? 0) * ($detail->jumlah ?? 0);

                $price = $detail->harga_promo > 0 ? $detail->harga_promo : $detail->harga;
                $sub = $price * ($detail->jumlah ?? 0);
                if ($detail->diskon > 0) {
                    $sub -= ($sub * $detail->diskon / 100);
                }
                $promoMap[$key]['subtotal'] += $sub;
            } else {
                $price = $detail->harga;
                $sub = $price * ($detail->jumlah ?? 0);
                if ($detail->diskon > 0) {
                    $sub -= ($sub * $detail->diskon / 100);
                }
                $groupedItems[] = [
                    'isBundle' => false,
                    'detail' => $detail,
                    'price' => $price,
                    'diskon' => $detail->diskon ?? 0,
                    'totalQty' => $detail->jumlah ?? 0,
                    'totalBerat' => floatval($detail->barang->berat ?? 0) * ($detail->jumlah ?? 0),
                    'subtotal' => $sub,
                ];
            }
        }
        unset($promoMap);

        $grandTotal = ($order->total_harga ?? 0) - ($order->totalDiskon ?? 0) - ($order->diskonKodeUnik ?? 0) + ($order->biayaExpedisi ?? 0);

        return [
            'order' => $order,
            'groupedItems' => $groupedItems,
            'grandTotal' => $grandTotal
        ];
    }

    /**
     * Product Resume: summary of books sold from confirmed orders.
     */
    public function productResume(Request $request)
    {
        $month = $request->month ?? date('n');
        $year = $request->year ?? date('Y');

        $books = DB::table('old_orderdetail')
            ->join('old_order', 'old_orderdetail.code_order', '=', 'old_order.id')
            ->leftJoin('old_ms_barang', 'old_orderdetail.code_barang', '=', 'old_ms_barang.id')
            ->select(
                'old_ms_barang.judul_buku as nama_buku',
                DB::raw('SUM(old_orderdetail.jumlah) as total_jumlah'),
                DB::raw('SUM(old_orderdetail.jumlah * old_orderdetail.Harga) as total_harga')
            )
            ->where('old_order.resume_status', 1)
            ->whereMonth('old_order.created_at', $month)
            ->whereYear('old_order.created_at', $year)
            ->groupBy('old_orderdetail.code_barang', 'old_ms_barang.judul_buku')
            ->orderBy('total_jumlah', 'desc')
            ->get();

        return Inertia::render('Dashboard/OldOrders/ProductResume', [
            'books' => $books,
            'filter' => [
                'month' => (int) $month,
                'year' => (int) $year,
            ]
        ]);
    }

    /**
     * Export resume to Excel.
     */
    public function exportResumeExcel($year, $month)
    {
        $summary = DB::table('old_order')
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) as total_nominal'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN 1 ELSE 0 END) as true_orders'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN total_barang ELSE 0 END) as true_barang'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN (total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) ELSE 0 END) as true_nominal')
            )
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->first();

        $orders = OldOrder::with('customer')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->get();

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $monthName = $monthNames[(int) $month] ?? $month;

        $data = [
            ['<b>RINGKASAN ORDER - ' . strtoupper($monthName) . ' ' . $year . '</b>'],
            [''],
            ['<b>Kategori</b>', '<b>Jumlah Order</b>', '<b>Jumlah Buku</b>', '<b>Nominal</b>'],
            ['Semua Order', $summary->total_orders, (int) $summary->total_barang, 'Rp ' . number_format($summary->total_nominal, 0, ',', '.')],
            ['Order Aktif (Status True)', (int) $summary->true_orders, (int) $summary->true_barang, 'Rp ' . number_format($summary->true_nominal, 0, ',', '.')],
            [''],
            [''],
            ['<b>DAFTAR ORDER DETAIL</b>'],
            [''],
            ['<b>No</b>', '<b>Status</b>', '<b>Code Order</b>', '<b>Customer</b>', '<b>Total Barang</b>', '<b>Nominal</b>', '<b>Tanggal</b>']
        ];

        $no = 1;
        foreach ($orders as $order) {
            $nominal = $order->total_harga + ($order->biayaExpedisi ?? 0) - ($order->totalDiskon ?? 0) - ($order->diskonKodeUnik ?? 0);
            $data[] = [
                $no++,
                $order->resume_status ? 'True' : 'False',
                $order->id,
                $order->customer?->nama ?? '-',
                $order->total_barang,
                'Rp ' . number_format($nominal, 0, ',', '.'),
                $order->created_at->format('d/m/Y')
            ];
        }

        $filename = 'resume_order_' . $monthName . '_' . $year . '_' . date('His') . '.xlsx';
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);

        // Stream directly to browser (more reliable on shared hosting)
        while (ob_get_level()) {
            ob_end_clean();
        }

        return response()->streamDownload(function () use ($xlsx) {
            $xlsx->saveAs('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export product resume to Excel.
     */
    public function exportProductResumeExcel(Request $request)
    {
        $month = $request->month ?? date('n');
        $year = $request->year ?? date('Y');

        $books = DB::table('old_orderdetail')
            ->join('old_order', 'old_orderdetail.code_order', '=', 'old_order.id')
            ->leftJoin('old_ms_barang', 'old_orderdetail.code_barang', '=', 'old_ms_barang.id')
            ->select(
                'old_ms_barang.judul_buku as nama_buku',
                DB::raw('SUM(old_orderdetail.jumlah) as total_jumlah'),
                DB::raw('SUM(old_orderdetail.jumlah * old_orderdetail.Harga) as total_harga')
            )
            ->where('old_order.resume_status', 1)
            ->whereMonth('old_order.created_at', $month)
            ->whereYear('old_order.created_at', $year)
            ->groupBy('old_orderdetail.code_barang', 'old_ms_barang.judul_buku')
            ->orderBy('total_jumlah', 'desc')
            ->get();

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $monthName = $monthNames[(int) $month] ?? $month;

        $data = [
            ['<b>LAPORAN PRODUK RESUME - ' . strtoupper($monthName) . ' ' . $year . '</b>'],
            [''],
            ['<b>No</b>', '<b>Nama Buku</b>', '<b>Jumlah Buku</b>', '<b>Total Harga Buku</b>']
        ];

        $no = 1;
        $totalQty = 0;
        $totalAmount = 0;
        foreach ($books as $book) {
            $qty = (int) $book->total_jumlah;
            $amount = (float) $book->total_harga;
            $data[] = [
                $no++,
                $book->nama_buku ?? 'Tanpa Judul',
                $qty,
                'Rp ' . number_format($amount, 0, ',', '.')
            ];
            $totalQty += $qty;
            $totalAmount += $amount;
        }

        $data[] = [''];
        $data[] = ['', '<b>TOTAL KESELURUHAN</b>', '<b>' . $totalQty . '</b>', '<b>Rp ' . number_format($totalAmount, 0, ',', '.') . '</b>'];

        $filename = 'produk_resume_' . $monthName . '_' . $year . '_' . date('His') . '.xlsx';
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);

        // Stream directly to browser (more reliable on shared hosting)
        while (ob_get_level()) {
            ob_end_clean();
        }

        return response()->streamDownload(function () use ($xlsx) {
            $xlsx->saveAs('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export resume report (book sales) to Excel.
     */
    public function exportResumeReportExcel(Request $request)
    {
        $month = $request->month ?? date('n');
        $year = $request->year ?? date('Y');
        $resumeStatus = $request->has('resume_status') ? $request->boolean('resume_status') : true;

        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $items = DB::table('old_orderdetail')
            ->join('old_order', 'old_orderdetail.code_order', '=', 'old_order.id')
            ->join('old_ms_barang', 'old_orderdetail.code_barang', '=', 'old_ms_barang.id')
            ->select(
                'old_ms_barang.judul_buku as nama_buku',
                DB::raw('SUM(old_orderdetail.jumlah) as jumlah_buku'),
                DB::raw('SUM(
                    (CASE WHEN old_orderdetail.harga_promo > 0 THEN old_orderdetail.harga_promo ELSE old_orderdetail.Harga END) 
                    * old_orderdetail.jumlah 
                    * (1 - COALESCE(old_orderdetail.diskon, 0) / 100)
                ) as total_harga_buku')
            )
            ->whereBetween('old_order.created_at', [$startDate, $endDate])
            ->where('old_order.resume_status', $resumeStatus)
            ->groupBy('old_ms_barang.judul_buku')
            ->orderBy('jumlah_buku', 'desc')
            ->get();

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $monthName = $monthNames[(int) $month] ?? $month;
        $statusLabel = $resumeStatus ? 'Aktif' : 'Tidak Aktif';

        $data = [
            ['<b>LAPORAN RESUME OLD ORDER - ' . strtoupper($monthName) . ' ' . $year . '</b>'],
            ['Status: ' . $statusLabel],
            [''],
            ['<b>No</b>', '<b>Nama Buku</b>', '<b>Jumlah Buku</b>', '<b>Total Harga Buku</b>']
        ];

        $no = 1;
        $totalQty = 0;
        $totalAmount = 0;
        foreach ($items as $item) {
            $qty = (int) $item->jumlah_buku;
            $amount = (float) $item->total_harga_buku;
            $data[] = [
                $no++,
                $item->nama_buku ?? 'Tanpa Judul',
                $qty,
                'Rp ' . number_format($amount, 0, ',', '.')
            ];
            $totalQty += $qty;
            $totalAmount += $amount;
        }

        $data[] = [''];
        $data[] = ['', '<b>TOTAL KESELURUHAN</b>', '<b>' . $totalQty . '</b>', '<b>Rp ' . number_format($totalAmount, 0, ',', '.') . '</b>'];

        $filename = 'laporan_resume_' . $monthName . '_' . $year . '_' . date('His') . '.xlsx';
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);

        while (ob_get_level()) {
            ob_end_clean();
        }

        return response()->streamDownload(function () use ($xlsx) {
            $xlsx->saveAs('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
