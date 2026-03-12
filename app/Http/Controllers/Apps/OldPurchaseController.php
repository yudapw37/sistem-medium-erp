<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldPurchase;
use App\Models\OldPurchaseDetail;
use App\Models\OldPurchaseAktif;
use App\Models\OldPurchaseAktifDetail;
use App\Models\OldBarang;
use App\Models\OldStockRunning;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Shuchkin\SimpleXLSXGen;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\DB;

class OldPurchaseController extends Controller
{
    public function index()
    {
        $oldPurchases = OldPurchase::when(request()->q, function ($query) {
            $query->where('supplier', 'like', '%' . request()->q . '%')
                ->orWhere('nomor_faktur', 'like', '%' . request()->q . '%');
        })->latest()->paginate(10);

        return Inertia::render('Dashboard/OldPurchases/Index', [
            'oldPurchases' => $oldPurchases
        ]);
    }

    public function uploadForm()
    {
        return Inertia::render('Dashboard/OldPurchases/Upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        // Increase resources for PDF parsing
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        // Check required extensions
        if (!extension_loaded('mbstring')) {
            return response()->json(['error' => 'extension_missing', 'message' => 'PHP extension "mbstring" tidak terpasang di server.'], 500);
        }
        if (!extension_loaded('gd') && !extension_loaded('zlib')) {
            // zlib is often used for compressed PDFs
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'parse_error',
                'message' => 'Gagal membaca PDF: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }

        $filename = $request->file('file')->getClientOriginalName();

        $extractedData = [];

        // Phase 1: Merge multi-page invoices
        // Detect "X dariY" pattern (e.g. "1  dari2") to identify continuation pages
        $invoiceTexts = [];
        $currentText = '';
        $startPage = 1;

        foreach ($pdf->getPages() as $index => $page) {
            $pageText = $page->getText();
            $currentText .= $pageText . "\n";

            // Check for "X dariY" pattern — if X < Y, this page continues to the next
            if (preg_match('/(\d+)\s+dari\s*(\d+)/', $pageText, $pageIndicator)) {
                $currentPage = (int) $pageIndicator[1];
                $totalPages = (int) $pageIndicator[2];
                if ($currentPage < $totalPages) {
                    continue; // Still has continuation pages, keep merging
                }
            }

            // Invoice is complete (either last page of multi-page or single-page)
            $invoiceTexts[] = [
                'text' => $currentText,
                'start_page' => $startPage,
            ];
            $currentText = '';
            $startPage = $index + 2; // Next invoice starts on the next page
        }

        // If there's remaining text (shouldn't happen, but safety net)
        if (!empty(trim($currentText))) {
            $invoiceTexts[] = [
                'text' => $currentText,
                'start_page' => $startPage,
            ];
        }

        // Phase 2: Parse each merged invoice text
        foreach ($invoiceTexts as $invoiceData) {
            $text = $invoiceData['text'];

            // Basic extraction logic based on "Faktur Pajak" structure
            // Nomor Faktur
            preg_match('/Kode dan Nomor Seri Faktur Pajak : ([\d\.\-]+)/', $text, $matches);
            $nomorFaktur = $matches[1] ?? null;

            // Check if nomor_faktur already exists in database
            if ($nomorFaktur) {
                $exists = OldPurchase::where('nomor_faktur', $nomorFaktur)->exists();
                if ($exists) {
                    return response()->json([
                        'error' => 'duplicate',
                        'message' => 'Nomor faktur "' . $nomorFaktur . '" sudah pernah diimport.',
                        'nomor_faktur' => $nomorFaktur
                    ], 422);
                }
            }

            // Supplier
            preg_match('/Nama : (PT\s+MACANANJAYA\s+CEMERLANG)/', $text, $matches);
            $supplier = $matches[1] ?? 'PT MACANANJAYA CEMERLANG';

            // Dates - looking for "16 Oktober 2023" anywhere
            preg_match('/(\d{1,2})\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+(\d{4})/i', $text, $matches);
            $tanggalString = $matches[0] ?? null;
            $tanggalFaktur = $this->parseIndonesianDate($tanggalString);

            // Totals
            preg_match('/Harga Jual \/ Penggantian\s+([\d\.,]+)/', $text, $matches);
            $hargaJualVal = $this->parseNumber($matches[1] ?? '0');

            preg_match('/Total PPN\s+([\d\.,]+)/', $text, $matches);
            $totalPpn = $this->parseNumber($matches[1] ?? '0');

            $hargaTotal = $hargaJualVal;
            $ppn = $totalPpn;
            $subtotal = $hargaTotal - $ppn;

            // Items
            $items = [];

            preg_match_all('/Rp\s+([\d\.,]+)\s+x\s+([\d\.,]+)/', $text, $itemMatches, PREG_OFFSET_CAPTURE);

            foreach ($itemMatches[0] as $matchIndex => $matchInfo) {
                $fullMatch = $matchInfo[0];
                $offset = $matchInfo[1];

                $pricePerUnit = $this->parseNumber($itemMatches[1][$matchIndex][0]);
                $qty = $this->parseNumber($itemMatches[2][$matchIndex][0]);

                $beforeText = substr($text, 0, $offset);
                $linesBefore = explode("\n", trim($beforeText));

                $itemName = "";
                $foundIndex = -1;
                for ($i = count($linesBefore) - 1; $i >= 0; $i--) {
                    $line = trim($linesBefore[$i]);
                    if (preg_match('/^[\d\.,]+[\d]$/', $line)) {
                        $foundIndex = $i;
                        break;
                    }
                    if ($line == "Nama Barang Kena Pajak / Jasa Kena Pajak") {
                        $foundIndex = $i;
                        break;
                    }
                }

                if ($foundIndex !== -1) {
                    $nameLines = array_slice($linesBefore, $foundIndex + 1);
                    $itemName = implode(" ", $nameLines);
                }

                $itemTotal = $pricePerUnit * $qty;

                $items[] = [
                    'nama' => trim($itemName),
                    'qty' => $qty,
                    'harga_satuan' => $pricePerUnit,
                    'total' => $itemTotal
                ];
            }

            $extractedData[] = [
                'pdf_page' => $invoiceData['start_page'],
                'nomor_faktur' => $nomorFaktur,
                'supplier' => $supplier,
                'tanggal_faktur' => $tanggalFaktur,
                'harga_total' => $hargaTotal,
                'ppn' => $ppn,
                'subtotal' => $subtotal,
                'items' => $items
            ];
        }

        return response()->json([
            'filename' => $filename,
            'data' => $extractedData
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchases' => 'required|array',
            'filename' => 'required|string',
            'purchases.*.items' => 'required|array',
            'purchases.*.items.*.nama' => 'required|string',
            'purchases.*.items.*.qty' => 'required|integer',
            'purchases.*.items.*.harga_satuan' => 'required|numeric',
            'purchases.*.items.*.total' => 'required|numeric',
        ]);

        // Check for duplicate nomor_faktur before saving
        foreach ($request->purchases as $pData) {
            if (!empty($pData['nomor_faktur'])) {
                $exists = OldPurchase::where('nomor_faktur', $pData['nomor_faktur'])->exists();
                if ($exists) {
                    return response()->json([
                        'error' => 'duplicate',
                        'message' => 'Nomor faktur "' . $pData['nomor_faktur'] . '" sudah pernah diimport.',
                        'nomor_faktur' => $pData['nomor_faktur']
                    ], 422);
                }
            }
        }

        DB::transaction(function () use ($request) {
            foreach ($request->purchases as $pData) {
                $purchase = OldPurchase::create([
                    'nomor_faktur' => $pData['nomor_faktur'],
                    'supplier' => $pData['supplier'],
                    'tanggal_faktur' => $pData['tanggal_faktur'],
                    'harga_total' => $pData['harga_total'],
                    'ppn' => $pData['ppn'],
                    'subtotal' => $pData['subtotal'],
                    'pdf_filename' => $request->filename,
                    'pdf_page' => $pData['pdf_page'],
                ]);

                foreach ($pData['items'] as $iData) {
                    // Look up code_barang from mapping table if not provided
                    $codeBarang = $iData['code_barang'] ?? null;
                    if (!$codeBarang && !empty($iData['nama'])) {
                        $mapping = DB::table('old_ms_barang_purchase')
                            ->where('nama_barang', $iData['nama'])
                            ->whereNotNull('code_barang')
                            ->where('code_barang', '!=', '')
                            ->first();
                        if ($mapping) {
                            $codeBarang = $mapping->code_barang;
                        }
                    }

                    $purchase->details()->create([
                        'code_barang' => $codeBarang,
                        'nama' => $iData['nama'],
                        'qty' => $iData['qty'],
                        'harga_satuan' => $iData['harga_satuan'],
                        'total' => $iData['total'],
                    ]);
                }
            }
        });

        return redirect()->route('old-purchases.index')->with('success', 'Data purchase berhasil disimpan.');
    }

    public function show($id)
    {
        $oldPurchase = OldPurchase::with('details')->findOrFail($id);

        return Inertia::render('Dashboard/OldPurchases/Show', [
            'oldPurchase' => $oldPurchase
        ]);
    }

    public function destroy($id)
    {
        $oldPurchase = OldPurchase::findOrFail($id);
        $oldPurchase->delete();

        return redirect()->route('old-purchases.index')->with('success', 'Data purchase berhasil dihapus.');
    }

    /**
     * Resume: monthly summary of old purchases.
     */
    public function resume()
    {
        $months = DB::table('old_purchases')
            ->select(
                DB::raw('YEAR(tanggal_faktur) as year'),
                DB::raw('MONTH(tanggal_faktur) as month'),
                // All statuses
                DB::raw('COUNT(*) as total_purchases'),
                DB::raw('SUM(harga_total) as total_nominal'),
                // Only resume_status = true
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN 1 ELSE 0 END) as true_purchases'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN harga_total ELSE 0 END) as true_nominal')
            )
            ->whereNotNull('tanggal_faktur')
            ->groupBy(DB::raw('YEAR(tanggal_faktur)'), DB::raw('MONTH(tanggal_faktur)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return Inertia::render('Dashboard/OldPurchases/Resume', [
            'months' => $months,
        ]);
    }

    /**
     * Resume Detail: list purchases for a specific month, with item details.
     */
    public function resumeDetail($year, $month)
    {
        $purchases = OldPurchase::with('details')
            ->whereYear('tanggal_faktur', $year)
            ->whereMonth('tanggal_faktur', $month)
            ->orderBy('tanggal_faktur', 'desc')
            ->get();

        return response()->json($purchases);
    }

    /**
     * Toggle resume_status for a single old purchase.
     */
    public function toggleResumeStatus(Request $request, $id)
    {
        $purchase = OldPurchase::findOrFail($id);
        $purchase->resume_status = $request->boolean('resume_status');
        $purchase->save();

        // Return updated monthly summary for the month
        $year = $purchase->tanggal_faktur->year;
        $month = $purchase->tanggal_faktur->month;

        $summary = DB::table('old_purchases')
            ->select(
                DB::raw('COUNT(*) as total_purchases'),
                DB::raw('SUM(harga_total) as total_nominal'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN 1 ELSE 0 END) as true_purchases'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN harga_total ELSE 0 END) as true_nominal')
            )
            ->whereYear('tanggal_faktur', $year)
            ->whereMonth('tanggal_faktur', $month)
            ->first();

        return response()->json([
            'purchase' => $purchase,
            'summary' => $summary,
        ]);
    }

    /**
     * Bulk toggle resume_status for all purchases in a specific month.
     */
    public function bulkToggleResumeStatus(Request $request, $year, $month)
    {
        $newStatus = $request->boolean('resume_status');

        $updated = OldPurchase::whereYear('tanggal_faktur', $year)
            ->whereMonth('tanggal_faktur', $month)
            ->update(['resume_status' => $newStatus]);

        $summary = DB::table('old_purchases')
            ->select(
                DB::raw('COUNT(*) as total_purchases'),
                DB::raw('SUM(harga_total) as total_nominal'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN 1 ELSE 0 END) as true_purchases'),
                DB::raw('SUM(CASE WHEN resume_status = 1 THEN harga_total ELSE 0 END) as true_nominal')
            )
            ->whereYear('tanggal_faktur', $year)
            ->whereMonth('tanggal_faktur', $month)
            ->first();

        return response()->json([
            'updated' => $updated,
            'summary' => $summary,
        ]);
    }

    /**
     * Sync active purchases for a specific month to old_purchase_aktif.
     * Also removes non-final purchases that are no longer active.
     */
    public function syncMonth($year, $month)
    {
        $added = 0;
        $removed = 0;
        $skipped = [];

        DB::transaction(function () use ($year, $month, &$added, &$removed, &$skipped) {
            // 1. ADD: active purchases not yet in old_purchase_aktif
            $purchasesToAdd = OldPurchase::with('details')
                ->where('resume_status', true)
                ->whereYear('tanggal_faktur', $year)
                ->whereMonth('tanggal_faktur', $month)
                ->whereDoesntHave('aktif')
                ->get();

            foreach ($purchasesToAdd as $purchase) {
                // Check: all details must have code_barang
                $unmapped = $purchase->details->filter(function ($d) {
                    return empty($d->code_barang);
                });

                if ($unmapped->count() > 0) {
                    $skipped[] = $purchase->nomor_faktur ?: "ID #{$purchase->id}";
                    continue;
                }

                $aktif = OldPurchaseAktif::create([
                    'old_purchase_id' => $purchase->id,
                    'nomor_faktur'    => $purchase->nomor_faktur,
                    'supplier'        => $purchase->supplier,
                    'tanggal_faktur'  => $purchase->tanggal_faktur,
                    'harga_total'     => $purchase->harga_total,
                    'ppn'             => $purchase->ppn,
                    'subtotal'        => $purchase->subtotal,
                    'is_final'        => false,
                ]);

                // Group details by code_barang to merge bundled items
                // e.g. "Cover Buku 20pcs" + "Isi Buku 20pcs" → "Buku Z 20pcs"
                $grouped = $purchase->details->groupBy('code_barang');

                foreach ($grouped as $codeBarang => $details) {
                    // Bundling logic: qty = MIN (components form 1 product, not sum)
                    $bundleQty = $details->min('qty');
                    // Total cost = SUM of all component costs
                    $totalAmount = $details->sum('total');
                    // Recalculate unit price from merged values
                    $hargaSatuan = $bundleQty > 0 ? round($totalAmount / $bundleQty, 2) : 0;

                    // Use master barang name if available
                    $barang = OldBarang::find($codeBarang);
                    $nama = $barang ? $barang->judul_buku : $details->first()->nama;

                    OldPurchaseAktifDetail::create([
                        'old_purchase_aktif_id' => $aktif->id,
                        'code_barang' => $codeBarang,
                        'nama'        => $nama,
                        'qty'         => $bundleQty,
                        'harga_satuan' => $hargaSatuan,
                        'total'       => $totalAmount,
                    ]);
                }

                $added++;
            }

            // 2. REMOVE: non-final purchases that are no longer active
            $toRemove = OldPurchaseAktif::where('is_final', false)
                ->whereHas('oldPurchase', function ($q) use ($year, $month) {
                    $q->where('resume_status', false)
                      ->whereYear('tanggal_faktur', $year)
                      ->whereMonth('tanggal_faktur', $month);
                })
                ->get();

            foreach ($toRemove as $aktif) {
                $aktif->details()->delete();
                $aktif->delete();
                $removed++;
            }
        });

        // Updated sync info
        $syncInfo = DB::table('old_purchase_aktif')
            ->join('old_purchases', 'old_purchase_aktif.old_purchase_id', '=', 'old_purchases.id')
            ->whereYear('old_purchases.tanggal_faktur', $year)
            ->whereMonth('old_purchases.tanggal_faktur', $month)
            ->selectRaw('
                COUNT(*) as synced_count,
                SUM(CASE WHEN old_purchase_aktif.is_final = 1 THEN 1 ELSE 0 END) as final_count
            ')
            ->first();

        return response()->json([
            'added'        => $added,
            'removed'      => $removed,
            'skipped'      => $skipped,
            'synced_count' => (int) ($syncInfo->synced_count ?? 0),
            'final_count'  => (int) ($syncInfo->final_count ?? 0),
        ]);
    }

    /**
     * Resume Report: aggregated book purchases (Yearly/Monthly).
     */
    public function resumeReport(Request $request)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month; // Optional month

        $query = DB::table('old_purchase_details')
            ->join('old_purchases', 'old_purchase_details.old_purchase_id', '=', 'old_purchases.id')
            ->select(
                'old_purchase_details.nama as nama_buku',
                DB::raw('SUM(old_purchase_details.qty) as total_qty'),
                DB::raw('SUM(old_purchase_details.total) as total_nominal')
            )
            ->where('old_purchases.resume_status', true)
            ->whereYear('old_purchases.tanggal_faktur', $year);

        if ($month) {
            $query->whereMonth('old_purchases.tanggal_faktur', $month);
        }

        $items = $query->groupBy('old_purchase_details.nama')
            ->orderBy('total_qty', 'desc')
            ->get();

        return Inertia::render('Dashboard/OldPurchases/ResumeReport', [
            'items' => $items,
            'filters' => [
                'year' => (int) $year,
                'month' => $month ? (int) $month : null,
            ],
        ]);
    }

    /**
     * Export resume to Excel (Requirement 1: Monthly purchase list).
     */
    public function exportResumeExcel($year, $month)
    {
        $purchases = OldPurchase::whereYear('tanggal_faktur', $year)
            ->whereMonth('tanggal_faktur', $month)
            ->orderBy('tanggal_faktur', 'asc')
            ->get();

        $monthName = date('F', mktime(0, 0, 0, $month, 10));

        $data = [
            ['<b>RESUME PEMBELIAN - ' . strtoupper($monthName) . ' ' . $year . '</b>'],
            [''],
            ['<b>No</b>', '<b>Status</b>', '<b>No Faktur</b>', '<b>Supplier</b>', '<b>Tanggal</b>', '<b>Nominal</b>']
        ];

        $no = 1;
        foreach ($purchases as $p) {
            $data[] = [
                $no++,
                $p->resume_status ? 'Aktif' : 'Non-Aktif',
                $p->nomor_faktur,
                $p->supplier,
                $p->tanggal_faktur->format('d/m/Y'),
                (float) $p->harga_total
            ];
        }

        return $this->downloadExcel($data, "resume_pembelian_{$month}_{$year}.xlsx");
    }

    /**
     * Export product resume to Excel (Requirement 2: Grouped by book).
     */
    public function exportProductResumeExcel(Request $request)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month;

        $items = DB::table('old_purchase_details')
            ->join('old_purchases', 'old_purchase_details.old_purchase_id', '=', 'old_purchases.id')
            ->select(
                'old_purchase_details.nama',
                DB::raw('SUM(old_purchase_details.qty) as total_qty'),
                DB::raw('SUM(old_purchase_details.total) as total_nominal')
            )
            ->where('old_purchases.resume_status', true)
            ->whereYear('old_purchases.tanggal_faktur', $year)
            ->when($month, fn($q) => $q->whereMonth('old_purchases.tanggal_faktur', $month))
            ->groupBy('old_purchase_details.nama')
            ->orderBy('total_qty', 'desc')
            ->get();

        $title = "LAPORAN PEMBELIAN BUKU - " . ($month ? "BULAN $month " : "") . $year;
        $data = [
            ["<b>$title</b>"],
            [''],
            ['<b>No</b>', '<b>Nama Buku</b>', '<b>Jumlah Qty</b>', '<b>Total Nominal</b>']
        ];

        $no = 1;
        foreach ($items as $item) {
            $data[] = [$no++, $item->nama, (int) $item->total_qty, (float) $item->total_nominal];
        }

        return $this->downloadExcel($data, "rekap_buku_pembelian_{$year}.xlsx");
    }

    /**
     * Export yearly report to Excel (Requirement 3: Yearly aggregates).
     */
    public function exportYearlyReportExcel(Request $request)
    {
        $year = $request->year ?? date('Y');

        $monthlyData = DB::table('old_purchases')
            ->select(
                DB::raw('MONTH(tanggal_faktur) as month'),
                DB::raw('SUM(harga_total) as total_nominal'),
                DB::raw('COUNT(*) as total_faktur')
            )
            ->where('resume_status', true)
            ->whereYear('tanggal_faktur', $year)
            ->groupBy(DB::raw('MONTH(tanggal_faktur)'))
            ->orderBy('month')
            ->get();

        $data = [
            ["<b>LAPORAN PEMBELIAN TAHUNAN - $year</b>"],
            [''],
            ['<b>Bulan</b>', '<b>Jumlah Faktur</b>', '<b>Total Nominal</b>']
        ];

        $monthNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

        foreach ($monthlyData as $row) {
            $data[] = [$monthNames[$row->month], (int) $row->total_faktur, (float) $row->total_nominal];
        }

        return $this->downloadExcel($data, "laporan_tahunan_pembelian_{$year}.xlsx");
    }

    private function downloadExcel($data, $filename)
    {
        $xlsx = SimpleXLSXGen::fromArray($data);

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

    private function parseNumber($str)
    {
        // Replace thousands separator (.) and decimal separator (,)
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);
        return (float) $str;
    }

    private function parseIndonesianDate($str)
    {
        if (!$str)
            return null;

        $months = [
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12'
        ];

        foreach ($months as $name => $val) {
            if (stripos($str, $name) !== false) {
                $str = str_ireplace($name, $val, $str);
                break;
            }
        }

        // Clean up text and reorder to Y-m-d
        // Expected format now: "16 10 2023" (with spaces or just digits)
        preg_match('/(\d{1,2})\s+(\d{2})\s+(\d{4})/', $str, $matches);
        if ($matches) {
            return "{$matches[3]}-{$matches[2]}-" . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        }

        return null;
    }
}
