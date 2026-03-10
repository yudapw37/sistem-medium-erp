<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldBarang;
use App\Models\OldOrderAktif;
use App\Models\OldOrderAktifDetail;
use App\Models\OldPurchaseAktif;
use App\Models\OldPurchaseAktifDetail;
use App\Models\OldStockAwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OldProfitReportController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = str_pad($request->input('month', date('m')), 2, '0', STR_PAD_LEFT);

        $summary = $this->calculateSummary($year, $month);

        return Inertia::render('Dashboard/OldProfitReport/Index', [
            'summary' => $summary,
            'filters' => [
                'year' => (int) $year,
                'month' => (int) $month,
            ]
        ]);
    }

    public function export(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = str_pad($request->input('month', date('m')), 2, '0', STR_PAD_LEFT);

        $summary = $this->calculateSummary($year, $month);

        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $exportData = [
            ['RESUME LABA KOTOR PENJUALAN OLD'],
            ['Periode:', $monthNames[$month] . ' ' . $year],
            [''],
            ['Keterangan', 'Kuantitas', 'Total'],
            ['PENJUALAN BARANG JADI / DAGANGAN', $summary['income']['qty'] . ' pcs', $summary['income']['total']],
            ['TOTAL PENDAPATAN', $summary['income']['qty'] . ' pcs', $summary['income']['total']],
            [''],
            ['PERHITUNGAN HPP'],
            ['Persediaan Awal Barang Jadi', $summary['inventory_start']['qty'] . ' pcs', $summary['inventory_start']['total']],
            ['Pembelian Barang Dagangan', $summary['purchase']['qty'] . ' pcs', $summary['purchase']['total']],
            ['Persediaan Akhir Barang Jadi', $summary['inventory_end']['qty'] . ' pcs', -$summary['inventory_end']['total']],
            ['HARGA POKOK PENJUALAN (HPP)', '', $summary['hpp']],
            [''],
            ['LABA KOTOR PENJUALAN', '', $summary['profit']],
        ];

        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($exportData);
        $filename = "Laba_Kotor_Old_{$year}_{$month}.xlsx";

        return response()->streamDownload(function () use ($xlsx) {
            $xlsx->download();
        }, $filename);
    }

    private function calculateSummary($year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // 1. PENDAPATAN (Income)
        $orders = OldOrderAktif::where('is_final', true)
            ->whereBetween('final_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $pendapatanQty = OldOrderAktifDetail::whereIn('old_order_aktif_id', $orders->pluck('id'))
            ->sum('jumlah');
        $pendapatanTotal = $orders->sum('total_harga');

        // 2. PERSEDIAAN AWAL
        $stockAwalQty = OldStockAwal::sum('qty');
        
        $purchasesBefore = OldPurchaseAktif::where('is_final', true)
            ->where('final_at', '<', $startDate . ' 00:00:00')
            ->get();
        $purchaseQtyBefore = OldPurchaseAktifDetail::whereIn('old_purchase_aktif_id', $purchasesBefore->pluck('id'))
            ->sum('qty');

        $salesBefore = OldOrderAktif::where('is_final', true)
            ->where('final_at', '<', $startDate . ' 00:00:00')
            ->get();
        $salesQtyBefore = OldOrderAktifDetail::whereIn('old_order_aktif_id', $salesBefore->pluck('id'))
            ->sum('jumlah');

        $persediaanAwalQty = $stockAwalQty + $purchaseQtyBefore - $salesQtyBefore;
        
        $persediaanAwalTotal = DB::table('old_stock_awal')
            ->join('old_ms_barang', 'old_stock_awal.code_barang', '=', 'old_ms_barang.id')
            ->select(DB::raw('SUM(old_stock_awal.qty * old_ms_barang.hpp) as total'))
            ->first()->total ?? 0;
            
        $historicalPurchasesValue = DB::table('old_purchase_aktif_details')
            ->join('old_purchase_aktif', 'old_purchase_aktif_details.old_purchase_aktif_id', '=', 'old_purchase_aktif.id')
            ->join('old_ms_barang', 'old_purchase_aktif_details.code_barang', '=', 'old_ms_barang.id')
            ->where('old_purchase_aktif.is_final', true)
            ->where('old_purchase_aktif.final_at', '<', $startDate . ' 00:00:00')
            ->select(DB::raw('SUM(old_purchase_aktif_details.qty * old_ms_barang.hpp) as total'))
            ->first()->total ?? 0;

        $historicalSalesValue = DB::table('old_order_aktif_details')
            ->join('old_order_aktif', 'old_order_aktif_details.old_order_aktif_id', '=', 'old_order_aktif.id')
            ->join('old_ms_barang', 'old_order_aktif_details.code_barang', '=', 'old_ms_barang.id')
            ->where('old_order_aktif.is_final', true)
            ->where('old_order_aktif.final_at', '<', $startDate . ' 00:00:00')
            ->select(DB::raw('SUM(old_order_aktif_details.jumlah * old_ms_barang.hpp) as total'))
            ->first()->total ?? 0;

        $persediaanAwalTotal = ($persediaanAwalTotal + $historicalPurchasesValue) - $historicalSalesValue;

        // 3. PEMBELIAN
        $purchases = OldPurchaseAktif::where('is_final', true)
            ->whereBetween('final_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();
        $pembelianQty = OldPurchaseAktifDetail::whereIn('old_purchase_aktif_id', $purchases->pluck('id'))
            ->sum('qty');
        $pembelianTotal = DB::table('old_purchase_aktif_details')
            ->join('old_purchase_aktif', 'old_purchase_aktif_details.old_purchase_aktif_id', '=', 'old_purchase_aktif.id')
            ->join('old_ms_barang', 'old_purchase_aktif_details.code_barang', '=', 'old_ms_barang.id')
            ->whereIn('old_purchase_aktif.id', $purchases->pluck('id'))
            ->select(DB::raw('SUM(old_purchase_aktif_details.qty * old_ms_barang.hpp) as total'))
            ->first()->total ?? 0;

        // 4. PERSEDIAAN AKHIR
        $persediaanAkhirQty = $persediaanAwalQty + $pembelianQty - $pendapatanQty;
        
        $persediaanAkhirTotal = DB::table('old_ms_barang')
            ->join(DB::raw("(SELECT code_barang, SUM(qty) as qty FROM (
                SELECT code_barang, qty FROM old_stock_awal 
                UNION ALL 
                SELECT code_barang, qty FROM old_purchase_aktif_details JOIN old_purchase_aktif ON old_purchase_aktif_details.old_purchase_aktif_id = old_purchase_aktif.id WHERE is_final = 1 AND final_at <= '$endDate 23:59:59'
                UNION ALL 
                SELECT code_barang, -jumlah as qty FROM old_order_aktif_details JOIN old_order_aktif ON old_order_aktif_details.old_order_aktif_id = old_order_aktif.id WHERE is_final = 1 AND final_at <= '$endDate 23:59:59'
            ) as t GROUP BY code_barang) as stock"), 'old_ms_barang.id', '=', 'stock.code_barang')
            ->select(DB::raw('SUM(stock.qty * old_ms_barang.hpp) as total'))
            ->first()->total ?? 0;

        // 5. HPP & LABA KOTOR
        $hppTotal = $persediaanAwalTotal + $pembelianTotal - $persediaanAkhirTotal;
        $labaKotor = $pendapatanTotal - $hppTotal;

        return [
            'income' => [
                'qty' => $pendapatanQty,
                'total' => (float)$pendapatanTotal,
            ],
            'inventory_start' => [
                'qty' => $persediaanAwalQty,
                'total' => (float)$persediaanAwalTotal,
            ],
            'purchase' => [
                'qty' => $pembelianQty,
                'total' => (float)$pembelianTotal,
            ],
            'inventory_end' => [
                'qty' => $persediaanAkhirQty,
                'total' => (float)$persediaanAkhirTotal,
            ],
            'hpp' => (float)$hppTotal,
            'profit' => (float)$labaKotor,
        ];
    }
}
