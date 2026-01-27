<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\AccountSetting;
use App\Models\Customer;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\ReceivablePayment;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReceivableController extends Controller
{
    /**
     * Display list of receivables (unpaid sales).
     */
    public function index(Request $request)
    {
        // Get sales with remaining balance
        $receivables = Sale::with(['customer', 'user'])
            ->whereIn('approval_status', ['waiting_stock', 'pending_warehouse', 'completed'])
            ->where(function ($q) {
                $q->where('payment_type', 'tempo')
                    ->orWhereRaw('COALESCE(grand_total, 0) > COALESCE((SELECT SUM(amount) FROM receivable_payments WHERE receivable_payments.sale_id = sales.id), 0)');
            })
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'unpaid') {
                    $q->whereRaw('COALESCE((SELECT SUM(amount) FROM receivable_payments WHERE receivable_payments.sale_id = sales.id), 0) = 0');
                } elseif ($request->status === 'partial') {
                    $q->whereRaw('COALESCE((SELECT SUM(amount) FROM receivable_payments WHERE receivable_payments.sale_id = sales.id), 0) > 0')
                      ->whereRaw('COALESCE((SELECT SUM(amount) FROM receivable_payments WHERE receivable_payments.sale_id = sales.id), 0) < grand_total');
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Calculate paid amount for each sale
        $receivables->getCollection()->transform(function ($sale) {
            $sale->paid_amount = ReceivablePayment::where('sale_id', $sale->id)->sum('amount');
            $sale->remaining = $sale->grand_total - $sale->paid_amount;
            $sale->is_paid = $sale->remaining <= 0;
            return $sale;
        });

        // Summary
        $totalReceivables = Sale::whereIn('approval_status', ['waiting_stock', 'pending_warehouse', 'completed'])
            ->where('payment_type', 'tempo')
            ->sum('grand_total');
        $totalPaid = ReceivablePayment::sum('amount');
        $totalRemaining = $totalReceivables - $totalPaid;

        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Dashboard/Receivables/Index', [
            'receivables' => $receivables,
            'summary' => [
                'total' => $totalReceivables,
                'paid' => $totalPaid,
                'remaining' => $totalRemaining,
            ],
            'customers' => $customers,
            'filters' => $request->only(['customer_id', 'status']),
        ]);
    }

    /**
     * Show receivable details with payment history.
     */
    public function show($saleId)
    {
        $sale = Sale::with(['customer', 'details.product', 'details.bundle'])->findOrFail($saleId);
        
        $payments = ReceivablePayment::with('user')
            ->where('sale_id', $saleId)
            ->orderBy('payment_date', 'desc')
            ->get();

        $paidAmount = $payments->sum('amount');
        $remaining = $sale->grand_total - $paidAmount;

        return Inertia::render('Dashboard/Receivables/Show', [
            'sale' => $sale,
            'payments' => $payments,
            'paidAmount' => $paidAmount,
            'remaining' => $remaining,
            'paymentMethods' => ReceivablePayment::PAYMENT_METHODS,
        ]);
    }

    /**
     * Store payment for a receivable.
     */
    public function storePayment(Request $request, $saleId)
    {
        $sale = Sale::with('customer')->findOrFail($saleId);
        
        $paidAmount = ReceivablePayment::where('sale_id', $saleId)->sum('amount');
        $remaining = $sale->grand_total - $paidAmount;

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1|max:' . $remaining,
            'payment_method' => 'required|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $sale) {
            $payment = ReceivablePayment::create([
                'reference' => ReceivablePayment::generateReference(),
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            // Create journal: Dr. Kas, Cr. Piutang
            $this->createPaymentJournal($payment);
        });

        return redirect()->route('receivables.show', $saleId)->with('success', 'Pembayaran berhasil dicatat.');
    }

    /**
     * Aging report for receivables.
     */
    public function aging(Request $request)
    {
        $today = Carbon::today();
        
        // Get all credit sales with remaining balance
        $sales = Sale::with('customer')
            ->whereIn('approval_status', ['waiting_stock', 'pending_warehouse', 'completed'])
            ->where('payment_type', 'tempo')
            ->get()
            ->map(function ($sale) use ($today) {
                $paidAmount = ReceivablePayment::where('sale_id', $sale->id)->sum('amount');
                $remaining = $sale->grand_total - $paidAmount;
                
                if ($remaining <= 0) return null;

                $daysPast = $today->diffInDays(Carbon::parse($sale->created_at), false) * -1;
                
                return [
                    'sale' => $sale,
                    'remaining' => $remaining,
                    'days_past' => $daysPast,
                    'aging_bucket' => $this->getAgingBucket($daysPast),
                ];
            })
            ->filter()
            ->values();

        // Group by aging bucket
        $agingData = [
            'current' => ['label' => '0-30 hari', 'items' => [], 'total' => 0],
            '31-60' => ['label' => '31-60 hari', 'items' => [], 'total' => 0],
            '61-90' => ['label' => '61-90 hari', 'items' => [], 'total' => 0],
            'over_90' => ['label' => '> 90 hari', 'items' => [], 'total' => 0],
        ];

        foreach ($sales as $item) {
            $bucket = $item['aging_bucket'];
            $agingData[$bucket]['items'][] = $item;
            $agingData[$bucket]['total'] += $item['remaining'];
        }

        return Inertia::render('Dashboard/Receivables/Aging', [
            'agingData' => $agingData,
            'totalReceivables' => $sales->sum('remaining'),
        ]);
    }

    /**
     * Customer card - receivables per customer.
     */
    public function customerCard(Request $request, $customerId)
    {
        $customer = Customer::findOrFail($customerId);
        
        $sales = Sale::where('customer_id', $customerId)
            ->whereIn('approval_status', ['waiting_stock', 'pending_warehouse', 'completed'])
            ->where('payment_type', 'tempo')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sale) {
                $paidAmount = ReceivablePayment::where('sale_id', $sale->id)->sum('amount');
                $sale->paid_amount = $paidAmount;
                $sale->remaining = $sale->grand_total - $paidAmount;
                return $sale;
            });

        $totalSales = $sales->sum('grand_total');
        $totalPaid = $sales->sum('paid_amount');
        $totalRemaining = $sales->sum('remaining');

        $payments = ReceivablePayment::with('sale')
            ->where('customer_id', $customerId)
            ->orderBy('payment_date', 'desc')
            ->take(20)
            ->get();

        return Inertia::render('Dashboard/Receivables/CustomerCard', [
            'customer' => $customer,
            'sales' => $sales,
            'payments' => $payments,
            'summary' => [
                'total_sales' => $totalSales,
                'total_paid' => $totalPaid,
                'total_remaining' => $totalRemaining,
            ],
        ]);
    }

    /**
     * Get aging bucket based on days past.
     */
    private function getAgingBucket($days)
    {
        if ($days <= 30) return 'current';
        if ($days <= 60) return '31-60';
        if ($days <= 90) return '61-90';
        return 'over_90';
    }

    /**
     * Create journal for receivable payment.
     */
    private function createPaymentJournal(ReceivablePayment $payment)
    {
        $cashAccountId = AccountSetting::getAccountId('sales_cash') ?: AccountSetting::getAccountId('cash');
        $receivableAccountId = AccountSetting::getAccountId('accounts_receivable');

        if (!$cashAccountId || !$receivableAccountId) return;

        $journal = Journal::create([
            'date' => $payment->payment_date,
            'reference' => Journal::generateReference('JRN-RCV'),
            'description' => 'Pembayaran piutang ' . $payment->sale->invoice,
            'source_type' => ReceivablePayment::class,
            'source_id' => $payment->id,
            'user_id' => auth()->id(),
        ]);

        // Dr. Kas
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $cashAccountId,
            'debit' => $payment->amount,
            'credit' => 0,
            'description' => 'Penerimaan pembayaran piutang',
        ]);

        // Cr. Piutang
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $receivableAccountId,
            'debit' => 0,
            'credit' => $payment->amount,
            'description' => 'Pelunasan piutang',
        ]);

        $journal->recalculateTotals();
    }

    /**
     * Display global receivable payment history.
     */
    public function paymentHistory(Request $request)
    {
        $payments = ReceivablePayment::with(['sale', 'customer', 'user'])
            ->when($request->q, function ($q, $invoice) {
                $q->whereHas('sale', function ($query) use ($invoice) {
                    $query->where('invoice', 'like', '%' . $invoice . '%');
                });
            })
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->start_date, fn($q) => $q->whereDate('payment_date', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('payment_date', '<=', $request->end_date))
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get(['id', 'name']);
        $paymentMethods = ReceivablePayment::PAYMENT_METHODS;

        return Inertia::render('Dashboard/Receivables/PaymentHistory', [
            'payments' => $payments,
            'customers' => $customers,
            'paymentMethods' => $paymentMethods,
            'filters' => $request->only(['q', 'customer_id', 'payment_method', 'start_date', 'end_date']),
        ]);
    }
}
