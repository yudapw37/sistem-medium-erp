<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OldStockReportController extends Controller
{
    /**
     * Laporan Persediaan Bulanan (Opsi A)
     *
     * Purchase: tanggal_faktur from old_purchase_aktif
     * Order: created_at from old_order
     */
    public function monthlyReport(Request $request)
    {
        $year = (int) ($request->year ?? date('Y'));
        $month = (int) ($request->month ?? date('n'));

        $reportData = $this->getMonthlyReportData($year, $month);

        return Inertia::render('Dashboard/OldStock/MonthlyReport', [
            'report'  => $reportData['data']->values(),
            'totals'  => $reportData['totals'],
            'filters' => ['year' => $year, 'month' => $month],
        ]);
    }

    public function monthlyReportExport(Request $request)
    {
        $year = (int) ($request->year ?? date('Y'));
        $month = (int) ($request->month ?? date('n'));

        $reportData = $this->getMonthlyReportData($year, $month);
        $data = $reportData['data'];
        $totals = $reportData['totals'];

        $monthNames = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $exportData = [
            ['LAPORAN PERSEDIAAN BULANAN'],
            ['Periode:', $monthNames[$month] . ' ' . $year],
            [''],
            ['No', 'Kode Barang', 'Nama Barang', 'Harga/HPP', 'Stock Awal', 'Nominal Awal', 'Masuk', 'Nominal Masuk', 'Keluar', 'Nominal Keluar', 'Stock Akhir', 'Nominal Akhir']
        ];

        foreach ($data as $idx => $item) {
            $exportData[] = [
                $idx + 1,
                $item->code_barang,
                $item->nama_barang,
                $item->hpp,
                $item->stock_awal,
                $item->nominal_awal,
                $item->stock_masuk,
                $item->nominal_masuk,
                $item->stock_keluar,
                $item->nominal_keluar,
                $item->stock_akhir,
                $item->nominal_akhir
            ];
        }

        $exportData[] = [''];
        $exportData[] = [
            '', '', 'TOTAL', '',
            $totals['stock_awal'],
            $totals['nominal_awal'],
            $totals['stock_masuk'],
            $totals['nominal_masuk'],
            $totals['stock_keluar'],
            $totals['nominal_keluar'],
            $totals['stock_akhir'],
            $totals['nominal_akhir']
        ];

        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($exportData);
        $filename = "Laporan_Persediaan_{$year}_{$month}.xlsx";

        return response()->streamDownload(function () use ($xlsx) {
            $xlsx->download();
        }, $filename);
    }

    private function getMonthlyReportData(int $year, int $month)
    {
        $startOfMonth = sprintf('%04d-%02d-01', $year, $month);
        $endOfMonth = date('Y-m-t', strtotime($startOfMonth));

        // Step 1: Collect ALL code_barang that have any activity (simple separate queries)
        $codes1 = DB::table('old_stock_running')->whereNotNull('code_barang')->pluck('code_barang');
        $codes2 = DB::table('old_stock_awal')->whereNotNull('code_barang')->pluck('code_barang');
        $codes3 = DB::table('old_purchase_aktif_details')
            ->whereNotNull('code_barang')->where('code_barang', '!=', '')
            ->distinct()->pluck('code_barang');
        $codes4 = DB::table('old_order_aktif_details')
            ->whereNotNull('code_barang')->where('code_barang', '!=', '')
            ->distinct()->pluck('code_barang');

        $allCodes = $codes1->merge($codes2)->merge($codes3)->merge($codes4)->unique()->filter()->values();

        if ($allCodes->isEmpty()) {
            return [
                'data' => collect([]),
                'totals' => [
                    'stock_awal' => 0, 'nominal_awal' => 0,
                    'stock_masuk' => 0, 'nominal_masuk' => 0,
                    'stock_keluar' => 0, 'nominal_keluar' => 0,
                    'stock_akhir' => 0, 'nominal_akhir' => 0
                ]
            ];
        }

        // Step 2: Pre-aggregate stock masuk (purchase aktif, by pa.tanggal_faktur)
        $masukSebelum = DB::table('old_purchase_aktif_details as pad')
            ->join('old_purchase_aktif as pa', 'pad.old_purchase_aktif_id', '=', 'pa.id')
            ->where('pa.is_final', 1)
            ->whereNotNull('pad.code_barang')
            ->where('pad.code_barang', '!=', '')
            ->whereDate('pa.tanggal_faktur', '<', $startOfMonth)
            ->select('pad.code_barang', DB::raw('SUM(pad.qty) as total'))
            ->groupBy('pad.code_barang')
            ->pluck('total', 'code_barang');

        $masukBulanIni = DB::table('old_purchase_aktif_details as pad')
            ->join('old_purchase_aktif as pa', 'pad.old_purchase_aktif_id', '=', 'pa.id')
            ->where('pa.is_final', 1)
            ->whereNotNull('pad.code_barang')
            ->where('pad.code_barang', '!=', '')
            ->whereDate('pa.tanggal_faktur', '>=', $startOfMonth)
            ->whereDate('pa.tanggal_faktur', '<=', $endOfMonth)
            ->select('pad.code_barang', DB::raw('SUM(pad.qty) as total'))
            ->groupBy('pad.code_barang')
            ->pluck('total', 'code_barang');

        // Step 3: Pre-aggregate stock keluar (order aktif, by old_order.created_at)
        $keluarSebelum = DB::table('old_order_aktif_details as oad')
            ->join('old_order_aktif as oa', 'oad.old_order_aktif_id', '=', 'oa.id')
            ->join('old_order as o', 'oa.old_order_id', '=', 'o.id')
            ->where('oa.is_final', 1)
            ->whereNotNull('oad.code_barang')
            ->where('oad.code_barang', '!=', '')
            ->whereDate('o.created_at', '<', $startOfMonth)
            ->select('oad.code_barang', DB::raw('SUM(oad.jumlah) as total'))
            ->groupBy('oad.code_barang')
            ->pluck('total', 'code_barang');

        $keluarBulanIni = DB::table('old_order_aktif_details as oad')
            ->join('old_order_aktif as oa', 'oad.old_order_aktif_id', '=', 'oa.id')
            ->join('old_order as o', 'oa.old_order_id', '=', 'o.id')
            ->where('oa.is_final', 1)
            ->whereNotNull('oad.code_barang')
            ->where('oad.code_barang', '!=', '')
            ->whereDate('o.created_at', '>=', $startOfMonth)
            ->whereDate('o.created_at', '<=', $endOfMonth)
            ->select('oad.code_barang', DB::raw('SUM(oad.jumlah) as total'))
            ->groupBy('oad.code_barang')
            ->pluck('total', 'code_barang');

        // Step 4: Get stock awal dasar
        $stockAwalDasar = DB::table('old_stock_awal')
            ->whereIn('code_barang', $allCodes)
            ->pluck('qty', 'code_barang');

        // Step 5: Get barang names and HPP
        $barangs = DB::table('old_ms_barang')
            ->whereIn('id', $allCodes)
            ->select('id', 'judul_buku', 'hpp')
            ->orderBy('judul_buku')
            ->get();

        // Step 6: Assemble report in PHP
        $data = $barangs->map(function ($b) use ($stockAwalDasar, $masukSebelum, $masukBulanIni, $keluarSebelum, $keluarBulanIni) {
            $awalDasar   = (float) ($stockAwalDasar[$b->id] ?? 0);
            $mSebelum    = (float) ($masukSebelum[$b->id] ?? 0);
            $kSebelum    = (float) ($keluarSebelum[$b->id] ?? 0);
            $mBulanIni   = (float) ($masukBulanIni[$b->id] ?? 0);
            $kBulanIni   = (float) ($keluarBulanIni[$b->id] ?? 0);

            $hpp        = (float) ($b->hpp ?? 0);

            $stockAwal  = $awalDasar + $mSebelum - $kSebelum;
            $stockAkhir = $stockAwal + $mBulanIni - $kBulanIni;

            return (object) [
                'code_barang'  => $b->id,
                'nama_barang'  => $b->judul_buku,
                'hpp'          => $hpp,
                'stock_awal'   => $stockAwal,
                'nominal_awal' => $stockAwal * $hpp,
                'stock_masuk'  => $mBulanIni,
                'nominal_masuk'=> $mBulanIni * $hpp,
                'stock_keluar' => $kBulanIni,
                'nominal_keluar'=> $kBulanIni * $hpp,
                'stock_akhir'  => $stockAkhir,
                'nominal_akhir'=> $stockAkhir * $hpp,
            ];
        });

        $totals = [
            'stock_awal'   => $data->sum('stock_awal'),
            'nominal_awal' => $data->sum('nominal_awal'),
            'stock_masuk'  => $data->sum('stock_masuk'),
            'nominal_masuk'=> $data->sum('nominal_masuk'),
            'stock_keluar' => $data->sum('stock_keluar'),
            'nominal_keluar'=> $data->sum('nominal_keluar'),
            'stock_akhir'  => $data->sum('stock_akhir'),
            'nominal_akhir'=> $data->sum('nominal_akhir'),
        ];

        return [
            'data'   => $data,
            'totals' => $totals
        ];
    }
}
