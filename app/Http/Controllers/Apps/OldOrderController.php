<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class OldOrderController extends Controller
{
    /**
     * Resume: monthly summary of old orders.
     */
    public function resume()
    {
        $months = DB::table('old_order')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                // All statuses
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_barang) as total_barang'),
                DB::raw('SUM(total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) as total_nominal'),
                // Only resume_status = true
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN 1 ELSE 0 END) as true_orders'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN total_barang ELSE 0 END) as true_barang'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN (total_harga + COALESCE(biayaExpedisi, 0) - COALESCE(totalDiskon, 0) - COALESCE(diskonKodeUnik, 0)) ELSE 0 END) as true_nominal')
            )
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return Inertia::render('Dashboard/OldOrders/Resume', [
            'months' => $months,
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
