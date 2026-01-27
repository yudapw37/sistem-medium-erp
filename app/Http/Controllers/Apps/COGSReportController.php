<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class COGSReportController extends Controller
{
    /**
     * Display HPP (COGS) report per product.
     */
    public function index(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $categoryId = $request->category_id;

        // Get HPP data per product from finalized sales
        $cogsData = SaleDetail::query()
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.status', 'finalized')
            ->whereBetween('sales.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNotNull('sale_details.product_id')
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('products.category_id', $categoryId);
            })
            ->select([
                'products.id as product_id',
                'products.barcode',
                'products.title',
                'products.buy_price as current_buy_price',
                'categories.name as category_name',
                DB::raw('SUM(sale_details.qty) as total_qty'),
                DB::raw('SUM(sale_details.qty * products.buy_price) as total_cogs'),
                DB::raw('AVG(sale_details.sell_price) as avg_sell_price'),
            ])
            ->groupBy('products.id', 'products.barcode', 'products.title', 'products.buy_price', 'categories.name')
            ->orderBy('total_cogs', 'desc')
            ->get()
            ->map(function ($item) {
                $item->margin = $item->avg_sell_price - $item->current_buy_price;
                $item->margin_percent = $item->current_buy_price > 0 
                    ? round(($item->margin / $item->current_buy_price) * 100, 2) 
                    : 0;
                return $item;
            });

        // Summary
        $totalCogs = $cogsData->sum('total_cogs');
        $totalQty = $cogsData->sum('total_qty');
        $productCount = $cogsData->count();

        // Get categories for filter
        $categories = Category::orderBy('name')->get();

        return Inertia::render('Dashboard/Reports/COGS', [
            'cogsData' => $cogsData,
            'summary' => [
                'totalCogs' => $totalCogs,
                'totalQty' => $totalQty,
                'productCount' => $productCount,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'category_id' => $categoryId,
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Export HPP report to Excel.
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $categoryId = $request->category_id;

        $cogsData = SaleDetail::query()
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.status', 'finalized')
            ->whereBetween('sales.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNotNull('sale_details.product_id')
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('products.category_id', $categoryId);
            })
            ->select([
                'products.barcode',
                'products.title',
                'categories.name as category_name',
                'products.buy_price as current_buy_price',
                DB::raw('SUM(sale_details.qty) as total_qty'),
                DB::raw('SUM(sale_details.qty * products.buy_price) as total_cogs'),
            ])
            ->groupBy('products.id', 'products.barcode', 'products.title', 'products.buy_price', 'categories.name')
            ->orderBy('total_cogs', 'desc')
            ->get();

        // Create Excel using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'Laporan HPP (Harga Pokok Penjualan)');
        $sheet->setCellValue('A2', 'Periode: ' . $startDate . ' s/d ' . $endDate);
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');

        // Table header
        $headers = ['Barcode', 'Nama Produk', 'Kategori', 'HPP/Unit', 'Qty Terjual', 'Total HPP'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $col++;
        }

        // Data
        $row = 5;
        $totalCogs = 0;
        foreach ($cogsData as $item) {
            $sheet->setCellValue('A' . $row, $item->barcode);
            $sheet->setCellValue('B' . $row, $item->title);
            $sheet->setCellValue('C' . $row, $item->category_name ?? '-');
            $sheet->setCellValue('D' . $row, $item->current_buy_price);
            $sheet->setCellValue('E' . $row, $item->total_qty);
            $sheet->setCellValue('F' . $row, $item->total_cogs);
            $totalCogs += $item->total_cogs;
            $row++;
        }

        // Total
        $sheet->setCellValue('E' . $row, 'TOTAL:');
        $sheet->setCellValue('F' . $row, $totalCogs);

        // Format
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A4:F4')->getFont()->setBold(true);
        $sheet->getStyle('D5:D' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F5:F' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'laporan_hpp_' . $startDate . '_' . $endDate . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export HPP report to PDF (print view).
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $categoryId = $request->category_id;

        $cogsData = SaleDetail::query()
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.status', 'finalized')
            ->whereBetween('sales.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNotNull('sale_details.product_id')
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('products.category_id', $categoryId);
            })
            ->select([
                'products.barcode',
                'products.title',
                'categories.name as category_name',
                'products.buy_price as current_buy_price',
                DB::raw('SUM(sale_details.qty) as total_qty'),
                DB::raw('SUM(sale_details.qty * products.buy_price) as total_cogs'),
            ])
            ->groupBy('products.id', 'products.barcode', 'products.title', 'products.buy_price', 'categories.name')
            ->orderBy('total_cogs', 'desc')
            ->get();

        $totalCogs = $cogsData->sum('total_cogs');
        $totalQty = $cogsData->sum('total_qty');

        return view('reports.cogs-pdf', [
            'cogsData' => $cogsData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalCogs' => $totalCogs,
            'totalQty' => $totalQty,
        ]);
    }
}
