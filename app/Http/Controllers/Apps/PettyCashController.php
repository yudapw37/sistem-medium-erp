<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountSetting;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\PettyCash;
use App\Models\PettyCashExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PettyCashController extends Controller
{
    /**
     * Display petty cash dashboard.
     */
    public function index(Request $request)
    {
        $activePettyCash = PettyCash::getActive();
        
        $pettyCashes = PettyCash::with('user')
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        // Get recent expenses if there's active period
        $recentExpenses = [];
        if ($activePettyCash) {
            $recentExpenses = PettyCashExpense::with(['user', 'approver'])
                ->where('petty_cash_id', $activePettyCash->id)
                ->latest('date')
                ->take(10)
                ->get();
        }

        return Inertia::render('Dashboard/PettyCash/Index', [
            'activePettyCash' => $activePettyCash,
            'remainingBalance' => $activePettyCash ? $activePettyCash->getRemainingBalance() : 0,
            'pettyCashes' => $pettyCashes,
            'recentExpenses' => $recentExpenses,
        ]);
    }

    /**
     * Open new petty cash period.
     */
    public function open(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        // Check if there's already an active period
        if (PettyCash::getActive()) {
            return redirect()->back()->with('error', 'Masih ada periode kas kecil yang aktif. Tutup periode sebelumnya terlebih dahulu.');
        }

        DB::transaction(function () use ($request) {
            $pettyCash = PettyCash::create([
                'reference' => PettyCash::generateReference('open'),
                'date' => now(),
                'type' => 'open',
                'amount' => $request->amount,
                'balance' => $request->amount,
                'description' => $request->description ?? 'Pembukaan kas kecil',
                'status' => 'active',
                'user_id' => auth()->id(),
            ]);

            // Create journal: Dr. Kas Kecil, Cr. Kas Besar
            $this->createJournal($pettyCash, 'open');
        });

        return redirect()->route('petty-cash.index')->with('success', 'Periode kas kecil berhasil dibuka.');
    }

    /**
     * Replenish petty cash.
     */
    public function replenish(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $activePettyCash = PettyCash::getActive();
        if (!$activePettyCash) {
            return redirect()->back()->with('error', 'Tidak ada periode kas kecil yang aktif.');
        }

        DB::transaction(function () use ($request, $activePettyCash) {
            // Update balance
            $activePettyCash->update([
                'amount' => $activePettyCash->amount + $request->amount,
                'balance' => $activePettyCash->balance + $request->amount,
            ]);

            // Create replenish record
            $replenish = PettyCash::create([
                'reference' => PettyCash::generateReference('replenish'),
                'date' => now(),
                'type' => 'replenish',
                'amount' => $request->amount,
                'balance' => $activePettyCash->balance + $request->amount,
                'description' => $request->description ?? 'Pengisian ulang kas kecil',
                'status' => 'active',
                'user_id' => auth()->id(),
            ]);

            // Create journal
            $this->createJournal($replenish, 'replenish');
        });

        return redirect()->route('petty-cash.index')->with('success', 'Kas kecil berhasil diisi ulang.');
    }

    /**
     * Show expense list for active period.
     */
    public function expenses(Request $request)
    {
        $activePettyCash = PettyCash::getActive();
        if (!$activePettyCash) {
            return redirect()->route('petty-cash.index')->with('error', 'Tidak ada periode kas kecil yang aktif.');
        }

        $expenses = PettyCashExpense::with(['user', 'approver'])
            ->where('petty_cash_id', $activePettyCash->id)
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Dashboard/PettyCash/Expenses', [
            'activePettyCash' => $activePettyCash,
            'remainingBalance' => $activePettyCash->getRemainingBalance(),
            'expenses' => $expenses,
            'categories' => PettyCashExpense::CATEGORIES,
            'filters' => $request->only(['category', 'status']),
        ]);
    }

    /**
     * Store new expense.
     */
    public function storeExpense(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:500',
            'receipt_number' => 'nullable|string|max:100',
            'date' => 'required|date',
        ]);

        $activePettyCash = PettyCash::getActive();
        if (!$activePettyCash) {
            return redirect()->back()->with('error', 'Tidak ada periode kas kecil yang aktif.');
        }

        // Check if enough balance
        $remainingBalance = $activePettyCash->getRemainingBalance();
        if ($request->amount > $remainingBalance) {
            return redirect()->back()->with('error', "Saldo tidak mencukupi. Sisa saldo: Rp " . number_format($remainingBalance, 0, ',', '.'));
        }

        PettyCashExpense::create([
            'reference' => PettyCashExpense::generateReference(),
            'petty_cash_id' => $activePettyCash->id,
            'date' => $request->date,
            'category' => $request->category,
            'amount' => $request->amount,
            'description' => $request->description,
            'receipt_number' => $request->receipt_number,
            'status' => 'pending',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('petty-cash.expenses')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    /**
     * Approve expense.
     */
    public function approveExpense($id)
    {
        $expense = PettyCashExpense::findOrFail($id);

        if (!$expense->isPending()) {
            return redirect()->back()->with('error', 'Pengeluaran sudah diproses.');
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pengeluaran berhasil disetujui.');
    }

    /**
     * Reject expense.
     */
    public function rejectExpense($id)
    {
        $expense = PettyCashExpense::findOrFail($id);

        if (!$expense->isPending()) {
            return redirect()->back()->with('error', 'Pengeluaran sudah diproses.');
        }

        $expense->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pengeluaran ditolak.');
    }

    /**
     * Show settlement/closing form.
     */
    public function settlement()
    {
        $activePettyCash = PettyCash::getActive();
        if (!$activePettyCash) {
            return redirect()->route('petty-cash.index')->with('error', 'Tidak ada periode kas kecil yang aktif.');
        }

        $expenses = PettyCashExpense::with(['user'])
            ->where('petty_cash_id', $activePettyCash->id)
            ->where('status', 'approved')
            ->get();

        // Group expenses by category
        $expensesByCategory = $expenses->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'label' => PettyCashExpense::CATEGORIES[$category] ?? $category,
                'total' => $items->sum('amount'),
                'count' => $items->count(),
            ];
        })->values();

        return Inertia::render('Dashboard/PettyCash/Settlement', [
            'activePettyCash' => $activePettyCash,
            'remainingBalance' => $activePettyCash->getRemainingBalance(),
            'totalAmount' => $activePettyCash->amount,
            'totalExpenses' => $expenses->sum('amount'),
            'expensesByCategory' => $expensesByCategory,
            'pendingCount' => PettyCashExpense::where('petty_cash_id', $activePettyCash->id)->where('status', 'pending')->count(),
        ]);
    }

    /**
     * Close petty cash period.
     */
    public function close(Request $request)
    {
        $activePettyCash = PettyCash::getActive();
        if (!$activePettyCash) {
            return redirect()->back()->with('error', 'Tidak ada periode kas kecil yang aktif.');
        }

        // Check for pending expenses
        $pendingCount = PettyCashExpense::where('petty_cash_id', $activePettyCash->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount > 0) {
            return redirect()->back()->with('error', "Masih ada {$pendingCount} pengeluaran yang belum disetujui.");
        }

        DB::transaction(function () use ($activePettyCash) {
            // Create closing journal entries for all approved expenses
            $this->createClosingJournal($activePettyCash);

            // Close the period
            $activePettyCash->update([
                'status' => 'closed',
                'balance' => $activePettyCash->getRemainingBalance(),
            ]);
        });

        return redirect()->route('petty-cash.index')->with('success', 'Periode kas kecil berhasil ditutup dan jurnal telah dibuat.');
    }

    /**
     * Create journal entry for open/replenish.
     */
    private function createJournal(PettyCash $pettyCash, $type)
    {
        $cashAccountId = AccountSetting::getAccountId('cash');
        // Find petty cash account or use cash account
        $pettyCashAccount = Account::where('name', 'like', '%Kas Kecil%')->first();
        $pettyCashAccountId = $pettyCashAccount ? $pettyCashAccount->id : $cashAccountId;

        $journal = Journal::create([
            'date' => $pettyCash->date,
            'reference' => Journal::generateReference('JRN-PC'),
            'description' => $pettyCash->description,
            'source_type' => PettyCash::class,
            'source_id' => $pettyCash->id,
            'user_id' => auth()->id(),
        ]);

        // Dr. Kas Kecil, Cr. Kas Besar
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $pettyCashAccountId,
            'debit' => $pettyCash->amount,
            'credit' => 0,
            'description' => 'Penerimaan kas kecil',
        ]);

        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $cashAccountId,
            'debit' => 0,
            'credit' => $pettyCash->amount,
            'description' => 'Transfer ke kas kecil',
        ]);

        $journal->recalculateTotals();
    }

    /**
     * Create closing journal entries for approved expenses.
     */
    private function createClosingJournal(PettyCash $pettyCash)
    {
        $expenses = PettyCashExpense::where('petty_cash_id', $pettyCash->id)
            ->where('status', 'approved')
            ->get();

        if ($expenses->isEmpty()) return;

        $pettyCashAccount = Account::where('name', 'like', '%Kas Kecil%')->first();
        $pettyCashAccountId = $pettyCashAccount ? $pettyCashAccount->id : AccountSetting::getAccountId('cash');

        // Get expense account
        $expenseAccount = Account::where('type', 'expense')
            ->where('name', 'like', '%Beban Operasional%')
            ->orWhere('name', 'like', '%Beban Umum%')
            ->first();
        $expenseAccountId = $expenseAccount ? $expenseAccount->id : null;

        if (!$expenseAccountId) return;

        $journal = Journal::create([
            'date' => now(),
            'reference' => Journal::generateReference('JRN-PC-CLOSE'),
            'description' => 'Pertanggungjawaban kas kecil periode ' . $pettyCash->reference,
            'source_type' => PettyCash::class,
            'source_id' => $pettyCash->id,
            'user_id' => auth()->id(),
        ]);

        $totalExpenses = $expenses->sum('amount');

        // Dr. Beban Operasional
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $expenseAccountId,
            'debit' => $totalExpenses,
            'credit' => 0,
            'description' => 'Pengeluaran kas kecil',
        ]);

        // Cr. Kas Kecil
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $pettyCashAccountId,
            'debit' => 0,
            'credit' => $totalExpenses,
            'description' => 'Pengeluaran kas kecil',
        ]);

        $journal->recalculateTotals();
    }
}
