<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldPurchase;
use App\Models\OldPurchaseDetail;
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

        $parser = new Parser();
        $pdf = $parser->parseFile($request->file('file')->getPathname());
        $filename = $request->file('file')->getClientOriginalName();

        $extractedData = [];

        foreach ($pdf->getPages() as $index => $page) {
            $text = $page->getText();

            // Basic extraction logic based on "Faktur Pajak" structure
            // Nomor Faktur
            preg_match('/Kode dan Nomor Seri Faktur Pajak : ([\d\.-]+)/', $text, $matches);
            $nomorFaktur = $matches[1] ?? null;

            // Supplier
            // Usually Macanan Jaya is the seller
            preg_match('/Nama : (PT\s+MACANANJAYA\s+CEMERLANG)/', $text, $matches);
            $supplier = $matches[1] ?? 'PT MACANANJAYA CEMERLANG';

            // Dates - looking for "16 Oktober 2023" anywhere
            preg_match('/(\d{1,2})\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+(\d{4})/i', $text, $matches);
            $tanggalString = $matches[0] ?? null;
            $tanggalFaktur = $this->parseIndonesianDate($tanggalString);

            // Totals
            // "Harga Jual / Penggantian	4.205.676,00"
            preg_match('/Harga Jual \/ Penggantian\s+([\d\.,]+)/', $text, $matches);
            $hargaJualVal = $this->parseNumber($matches[1] ?? '0');

            // "Total PPN	462.624,00"
            preg_match('/Total PPN\s+([\d\.,]+)/', $text, $matches);
            $totalPpn = $this->parseNumber($matches[1] ?? '0');

            // User mapping:
            // harga total = Harga Jual / Penggantian
            // ppn = Total PPN
            // subtotal = harga total - ppn
            $hargaTotal = $hargaJualVal;
            $ppn = $totalPpn;
            $subtotal = $hargaTotal - $ppn;

            // Items - Looking for blocks like:
            // "COVER BUKU HARDCOVER PILUNG MUSHAF AL-QURAN &"
            // "TERJEMAH (AL-KARIM)"
            // "Rp 5.135 x 819"
            // "4.205.676,001" (Price followed by index)

            // This is the tricky part. Let's use the sequence: Name -> Price per unit x Qty -> Total
            $items = [];

            // Regex for items: Captures the "Rp X x Y" line and tries to find the name before it
            // Pattern for Price and Qty: Rp 5.135 x 819
            preg_match_all('/Rp\s+([\d\.]+)\s+x\s+([\d\.]+)/', $text, $itemMatches, PREG_OFFSET_CAPTURE);

            foreach ($itemMatches[0] as $matchIndex => $matchInfo) {
                $fullMatch = $matchInfo[0];
                $offset = $matchInfo[1];

                $pricePerUnit = $this->parseNumber($itemMatches[1][$matchIndex][0]);
                $qty = $this->parseNumber($itemMatches[2][$matchIndex][0]);

                // Try to find the name before this line
                // The name is usually after "Nama Barang Kena Pajak / Jasa Kena Pajak" or after the previous item's total
                // In simple parsing, we can just split the text and look around.
                // For now, let's extract snippets.
                $beforeText = substr($text, 0, $offset);
                $linesBefore = explode("\n", trim($beforeText));

                // The name often spans multiple lines
                $itemName = "";
                $foundIndex = -1;
                for ($i = count($linesBefore) - 1; $i >= 0; $i--) {
                    $line = trim($linesBefore[$i]);
                    if (preg_match('/^[\d\.,]+[\d]$/', $line)) { // Total price from previous item or header
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
                'pdf_page' => $index + 1,
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
            'filename' => 'required|string'
        ]);

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
                    $purchase->details()->create([
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
     * Resume Detail: list purchases for a specific month.
     */
    public function resumeDetail($year, $month)
    {
        $purchases = OldPurchase::whereYear('tanggal_faktur', $year)
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
