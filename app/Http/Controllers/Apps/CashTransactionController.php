<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CashTransaction;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashTransactionController extends Controller
{
    /**
     * Display a listing of cash transactions.
     */
    public function index(Request $request)
    {
        $transactions = CashTransaction::with(['account', 'cashAccount', 'user'])
            ->when($request->q, function ($query) use ($request) {
                $query->where('reference', 'like', '%' . $request->q . '%')
                    ->orWhere('description', 'like', '%' . $request->q . '%');
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->start_date, function ($query) use ($request) {
                $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                $query->whereDate('date', '<=', $request->end_date);
            })
            ->latest('date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Dashboard/CashTransactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['q', 'type', 'status', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Display the specified cash transaction.
     */
    public function show($id)
    {
        $transaction = CashTransaction::with(['account', 'cashAccount', 'user', 'journal.entries.account'])
            ->findOrFail($id);

        return Inertia::render('Dashboard/CashTransactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Show the form for creating a new cash transaction.
     */
    public function create(Request $request)
    {
        $type = $request->type ?? 'out';
        
        // Get cash/bank accounts
        $cashAccounts = Account::where('type', 'asset')
            ->where(function ($q) {
                $q->where('code', 'like', '1-11%')
                    ->orWhere('code', 'like', '1-12%')
                    ->orWhere('name', 'like', '%Kas%')
                    ->orWhere('name', 'like', '%Bank%');
            })
            ->active()
            ->orderBy('code')
            ->get();

        // Get all accounts for counter account
        $accounts = Account::active()
            ->where('level', '>=', 2)
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/CashTransactions/Create', [
            'type' => $type,
            'cashAccounts' => $cashAccounts,
            'accounts' => $accounts,
            'reference' => CashTransaction::generateReference($type),
        ]);
    }

    /**
     * Store a newly created cash transaction (as draft).
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'cash_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $reference = CashTransaction::generateReference($request->type);
        
        // Create cash transaction as draft (no journal yet)
        CashTransaction::create([
            'reference' => $reference,
            'date' => $request->date,
            'type' => $request->type,
            'account_id' => $request->account_id,
            'cash_account_id' => $request->cash_account_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'status' => 'draft',
            'user_id' => auth()->id(),
        ]);

        $message = $request->type === 'in' ? 'Kas Masuk berhasil disimpan sebagai draft.' : 'Kas Keluar berhasil disimpan sebagai draft.';
        return redirect()->route('cash-transactions.index')->with('success', $message);
    }

    /**
     * Show the form for editing a cash transaction.
     */
    public function edit($id)
    {
        $transaction = CashTransaction::findOrFail($id);

        if ($transaction->isFinalized()) {
            return redirect()->route('cash-transactions.index')->with('error', 'Transaksi yang sudah final tidak dapat diedit.');
        }
        
        // Get cash/bank accounts
        $cashAccounts = Account::where('type', 'asset')
            ->where(function ($q) {
                $q->where('code', 'like', '1-11%')
                    ->orWhere('code', 'like', '1-12%')
                    ->orWhere('name', 'like', '%Kas%')
                    ->orWhere('name', 'like', '%Bank%');
            })
            ->active()
            ->orderBy('code')
            ->get();

        // Get all accounts for counter account
        $accounts = Account::active()
            ->where('level', '>=', 2)
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/CashTransactions/Edit', [
            'transaction' => $transaction,
            'cashAccounts' => $cashAccounts,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Update the specified cash transaction.
     */
    public function update(Request $request, $id)
    {
        $transaction = CashTransaction::findOrFail($id);

        if ($transaction->isFinalized()) {
            return redirect()->route('cash-transactions.index')->with('error', 'Transaksi yang sudah final tidak dapat diedit.');
        }

        $request->validate([
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'cash_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $transaction->update([
            'date' => $request->date,
            'account_id' => $request->account_id,
            'cash_account_id' => $request->cash_account_id,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return redirect()->route('cash-transactions.index')->with('success', 'Transaksi berhasil diupdate.');
    }

    /**
     * Finalize the cash transaction and create journal.
     */
    public function finalize($id)
    {
        $transaction = CashTransaction::findOrFail($id);

        if ($transaction->isFinalized()) {
            return redirect()->route('cash-transactions.index')->with('error', 'Transaksi sudah di-finalize.');
        }

        DB::transaction(function () use ($transaction) {
            // Create journal entry
            $journalPrefix = $transaction->type === 'in' ? 'JRN-CIN' : 'JRN-COUT';
            $journal = Journal::create([
                'date' => $transaction->date,
                'reference' => Journal::generateReference($journalPrefix),
                'description' => $transaction->description ?? ($transaction->type === 'in' ? 'Kas Masuk' : 'Kas Keluar'),
                'source_type' => CashTransaction::class,
                'source_id' => $transaction->id,
                'user_id' => auth()->id(),
            ]);

            if ($transaction->type === 'in') {
                // Kas Masuk: Debit Kas, Credit Akun Lawan
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $transaction->cash_account_id,
                    'debit' => $transaction->amount,
                    'credit' => 0,
                    'description' => 'Kas masuk',
                ]);
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $transaction->account_id,
                    'debit' => 0,
                    'credit' => $transaction->amount,
                    'description' => $transaction->description,
                ]);
            } else {
                // Kas Keluar: Debit Akun Lawan, Credit Kas
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $transaction->account_id,
                    'debit' => $transaction->amount,
                    'credit' => 0,
                    'description' => $transaction->description,
                ]);
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $transaction->cash_account_id,
                    'debit' => 0,
                    'credit' => $transaction->amount,
                    'description' => 'Kas keluar',
                ]);
            }

            $journal->recalculateTotals();

            // Update transaction status
            $transaction->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);
        });

        return redirect()->route('cash-transactions.index')->with('success', 'Transaksi berhasil di-finalize dan jurnal telah dibuat.');
    }

    /**
     * Remove the specified cash transaction.
     */
    public function destroy($id)
    {
        $transaction = CashTransaction::findOrFail($id);
        
        if ($transaction->isFinalized()) {
            return redirect()->route('cash-transactions.index')->with('error', 'Transaksi yang sudah final tidak dapat dihapus.');
        }

        $transaction->delete();

        return redirect()->route('cash-transactions.index')->with('success', 'Transaksi berhasil dihapus.');
    }
}
