<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\AccountSetting;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\PayablePayment;
use App\Models\Purchase;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PayableController extends Controller
{
    /**
     * Display list of payables (unpaid purchases).
     */
    public function index(Request $request)
    {
        $payables = Purchase::with(['supplier', 'user', 'warehouse'])
            ->where('status', 'finalized')
            ->where(function ($q) {
                $q->where('payment_type', 'tempo')
                    ->orWhereRaw('COALESCE(grand_total, 0) > COALESCE((SELECT SUM(amount) FROM payable_payments WHERE payable_payments.purchase_id = purchases.id), 0)');
            })
            ->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'unpaid') {
                    $q->whereRaw('COALESCE((SELECT SUM(amount) FROM payable_payments WHERE payable_payments.purchase_id = purchases.id), 0) = 0');
                } elseif ($request->status === 'partial') {
                    $q->whereRaw('COALESCE((SELECT SUM(amount) FROM payable_payments WHERE payable_payments.purchase_id = purchases.id), 0) > 0')
                      ->whereRaw('COALESCE((SELECT SUM(amount) FROM payable_payments WHERE payable_payments.purchase_id = purchases.id), 0) < grand_total');
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $payables->getCollection()->transform(function ($purchase) {
            $purchase->paid_amount = PayablePayment::where('purchase_id', $purchase->id)->sum('amount');
            $purchase->remaining = $purchase->grand_total - $purchase->paid_amount;
            $purchase->is_paid = $purchase->remaining <= 0;
            return $purchase;
        });

        // Summary
        $totalPayables = Purchase::where('status', 'finalized')
            ->where('payment_type', 'tempo')
            ->sum('grand_total');
        $totalPaid = PayablePayment::sum('amount');
        $totalRemaining = $totalPayables - $totalPaid;

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Dashboard/Payables/Index', [
            'payables' => $payables,
            'summary' => [
                'total' => $totalPayables,
                'paid' => $totalPaid,
                'remaining' => $totalRemaining,
            ],
            'suppliers' => $suppliers,
            'filters' => $request->only(['supplier_id', 'status']),
        ]);
    }

    /**
     * Show payable details with payment history.
     */
    public function show($purchaseId)
    {
        $purchase = Purchase::with(['supplier', 'details.product', 'user', 'warehouse'])->findOrFail($purchaseId);
        
        $payments = PayablePayment::with('user')
            ->where('purchase_id', $purchaseId)
            ->orderBy('payment_date', 'desc')
            ->get();

        $paidAmount = $payments->sum('amount');
        $remaining = $purchase->grand_total - $paidAmount;

        return Inertia::render('Dashboard/Payables/Show', [
            'purchase' => $purchase,
            'payments' => $payments,
            'paidAmount' => $paidAmount,
            'remaining' => $remaining,
            'paymentMethods' => PayablePayment::PAYMENT_METHODS,
        ]);
    }

    /**
     * Store payment for a payable.
     */
    public function storePayment(Request $request, $purchaseId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        
        $paidAmount = PayablePayment::where('purchase_id', $purchaseId)->sum('amount');
        $remaining = $purchase->grand_total - $paidAmount;

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1|max:' . $remaining,
            'payment_method' => 'required|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            $payment = PayablePayment::create([
                'reference' => PayablePayment::generateReference(),
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            // Create journal: Dr. Hutang, Cr. Kas
            $this->createPaymentJournal($payment);
        });

        return redirect()->route('payables.show', $purchaseId)->with('success', 'Pembayaran hutang berhasil dicatat.');
    }

    /**
     * Aging report for payables.
     */
    public function aging(Request $request)
    {
        $today = Carbon::today();
        
        $purchases = Purchase::with('supplier')
            ->where('status', 'finalized')
            ->where('payment_type', 'tempo')
            ->get()
            ->map(function ($purchase) use ($today) {
                $paidAmount = PayablePayment::where('purchase_id', $purchase->id)->sum('amount');
                $remaining = $purchase->grand_total - $paidAmount;
                
                if ($remaining <= 0) return null;

                $daysPast = $today->diffInDays(Carbon::parse($purchase->finalized_at ?? $purchase->created_at), false) * -1;
                
                return [
                    'purchase' => $purchase,
                    'remaining' => $remaining,
                    'days_past' => $daysPast,
                    'aging_bucket' => $this->getAgingBucket($daysPast),
                ];
            })
            ->filter()
            ->values();

        $agingData = [
            'current' => ['label' => '0-30 hari', 'items' => [], 'total' => 0],
            '31-60' => ['label' => '31-60 hari', 'items' => [], 'total' => 0],
            '61-90' => ['label' => '61-90 hari', 'items' => [], 'total' => 0],
            'over_90' => ['label' => '> 90 hari', 'items' => [], 'total' => 0],
        ];

        foreach ($purchases as $item) {
            $bucket = $item['aging_bucket'];
            $agingData[$bucket]['items'][] = $item;
            $agingData[$bucket]['total'] += $item['remaining'];
        }

        return Inertia::render('Dashboard/Payables/Aging', [
            'agingData' => $agingData,
            'totalPayables' => $purchases->sum('remaining'),
        ]);
    }

    /**
     * Supplier card - payables per supplier.
     */
    public function supplierCard(Request $request, $supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        
        $purchases = Purchase::where('supplier_id', $supplierId)
            ->where('status', 'finalized')
            ->where('payment_type', 'tempo')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($purchase) {
                $paidAmount = PayablePayment::where('purchase_id', $purchase->id)->sum('amount');
                $purchase->paid_amount = $paidAmount;
                $purchase->remaining = $purchase->grand_total - $paidAmount;
                return $purchase;
            });

        $totalPurchases = $purchases->sum('grand_total');
        $totalPaid = $purchases->sum('paid_amount');
        $totalRemaining = $purchases->sum('remaining');

        $payments = PayablePayment::with('purchase')
            ->where('supplier_id', $supplierId)
            ->orderBy('payment_date', 'desc')
            ->take(20)
            ->get();

        return Inertia::render('Dashboard/Payables/SupplierCard', [
            'supplier' => $supplier,
            'purchases' => $purchases,
            'payments' => $payments,
            'summary' => [
                'total_purchases' => $totalPurchases,
                'total_paid' => $totalPaid,
                'total_remaining' => $totalRemaining,
            ],
        ]);
    }

    /**
     * Get aging bucket.
     */
    private function getAgingBucket($days)
    {
        if ($days <= 30) return 'current';
        if ($days <= 60) return '31-60';
        if ($days <= 90) return '61-90';
        return 'over_90';
    }

    /**
     * Create journal for payable payment.
     */
    private function createPaymentJournal(PayablePayment $payment)
    {
        $cashAccountId = AccountSetting::getAccountId('cash'); // Or specific purchase payment account if added
        $payableAccountId = AccountSetting::getAccountId('accounts_payable');

        if (!$cashAccountId || !$payableAccountId) return;

        $journal = Journal::create([
            'date' => $payment->payment_date,
            'reference' => Journal::generateReference('JRN-PAY'),
            'description' => 'Pembayaran hutang ' . $payment->purchase->invoice,
            'source_type' => PayablePayment::class,
            'source_id' => $payment->id,
            'user_id' => auth()->id(),
        ]);

        // Dr. Hutang
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $payableAccountId,
            'debit' => $payment->amount,
            'credit' => 0,
            'description' => 'Pembayaran hutang supplier',
        ]);

        // Cr. Kas
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $cashAccountId,
            'debit' => 0,
            'credit' => $payment->amount,
            'description' => 'Pengeluaran kas untuk hutang',
        ]);

        $journal->recalculateTotals();
    }

    /**
     * Display global payable payment history.
     */
    public function paymentHistory(Request $request)
    {
        $payments = PayablePayment::with(['purchase', 'supplier', 'user'])
            ->when($request->q, function ($q, $invoice) {
                $q->whereHas('purchase', function ($query) use ($invoice) {
                    $query->where('invoice', 'like', '%' . $invoice . '%');
                });
            })
            ->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->start_date, fn($q) => $q->whereDate('payment_date', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('payment_date', '<=', $request->end_date))
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $paymentMethods = PayablePayment::PAYMENT_METHODS;

        return Inertia::render('Dashboard/Payables/PaymentHistory', [
            'payments' => $payments,
            'suppliers' => $suppliers,
            'paymentMethods' => $paymentMethods,
            'filters' => $request->only(['q', 'supplier_id', 'payment_method', 'start_date', 'end_date']),
        ]);
    }
}
