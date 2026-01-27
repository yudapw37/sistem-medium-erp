<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Purchase;
use App\Models\TaxSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxReportController extends Controller
{
    /**
     * Display the tax report
     */
    public function index(Request $request)
    {
        $taxEnabled = TaxSetting::isEnabled();
        
        // Get month/year from request or use current
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // PPN Keluaran (from sales/transactions)
        $ppnKeluaran = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->where('tax_amount', '>', 0)
            ->get();

        $totalPpnKeluaran = $ppnKeluaran->sum('tax_amount');
        $totalSalesSubtotal = $ppnKeluaran->sum('subtotal');
        $totalSales = $ppnKeluaran->sum('grand_total');

        // PPN Masukan (from purchases)
        $ppnMasukan = Purchase::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'finalized')
            ->where('tax_amount', '>', 0)
            ->get();

        $totalPpnMasukan = $ppnMasukan->sum('tax_amount');
        $totalPurchaseSubtotal = $ppnMasukan->sum('subtotal');
        $totalPurchases = $ppnMasukan->sum('grand_total');

        // Net Tax (PPN Keluaran - PPN Masukan)
        $netTax = $totalPpnKeluaran - $totalPpnMasukan;

        // Get available years for filter
        $years = collect(range(now()->year - 2, now()->year + 1));

        return Inertia::render('Dashboard/Reports/Tax', [
            'taxEnabled' => $taxEnabled,
            'month' => (int) $month,
            'year' => (int) $year,
            'years' => $years,
            'summary' => [
                'ppnKeluaran' => $totalPpnKeluaran,
                'salesSubtotal' => $totalSalesSubtotal,
                'salesTotal' => $totalSales,
                'salesCount' => $ppnKeluaran->count(),
                
                'ppnMasukan' => $totalPpnMasukan,
                'purchaseSubtotal' => $totalPurchaseSubtotal,
                'purchaseTotal' => $totalPurchases,
                'purchaseCount' => $ppnMasukan->count(),
                
                'netTax' => $netTax,
                'taxPayable' => $netTax > 0 ? $netTax : 0,
                'taxCreditable' => $netTax < 0 ? abs($netTax) : 0,
            ],
        ]);
    }
}
