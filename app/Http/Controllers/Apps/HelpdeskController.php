<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class HelpdeskController extends Controller
{
    /**
     * Display the helpdesk guide.
     */
    public function index()
    {
        return Inertia::render('Dashboard/Helpdesk/Index');
    }

    /**
     * Display the sales flow helpdesk guide.
     */
    public function penjualan()
    {
        return Inertia::render('Dashboard/Helpdesk/Penjualan');
    }

    /**
     * Display the purchase flow helpdesk guide.
     */
    public function pembelian()
    {
        return Inertia::render('Dashboard/Helpdesk/Pembelian');
    }

    /**
     * Display the stock opname helpdesk guide.
     */
    public function stockOpname()
    {
        return Inertia::render('Dashboard/Helpdesk/StockOpname');
    }

    /**
     * Display the stock penyesuaian helpdesk guide.
     */
    public function stockPenyesuaian()
    {
        return Inertia::render('Dashboard/Helpdesk/StockPenyesuaian');
    }

    /**
     * Display the zero-value transaction helpdesk guide.
     */
    public function zeroValueTransaction()
    {
        return Inertia::render('Dashboard/Helpdesk/ZeroValueTransaction');
    }
}
