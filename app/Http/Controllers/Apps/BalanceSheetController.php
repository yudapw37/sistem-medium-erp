<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BalanceSheetController extends Controller
{
    /**
     * Display balance sheet report.
     */
    public function index(Request $request)
    {
        $asOfDate = $request->date ?? now()->format('Y-m-d');

        // Get asset accounts with balances
        $assets = $this->getAccountsByType('asset', $asOfDate);
        $totalAssets = $assets->sum('balance');

        // Get liability accounts with balances
        $liabilities = $this->getAccountsByType('liability', $asOfDate);
        $totalLiabilities = $liabilities->sum('balance');

        // Get equity accounts with balances
        $equity = $this->getAccountsByType('equity', $asOfDate);
        $totalEquity = $equity->sum('balance');

        // Calculate retained earnings (profit/loss from revenue - expense)
        $retainedEarnings = $this->calculateRetainedEarnings($asOfDate);

        return Inertia::render('Dashboard/Reports/BalanceSheet', [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'retainedEarnings' => $retainedEarnings,
            'totalLiabilitiesAndEquity' => $totalLiabilities + $totalEquity + $retainedEarnings,
            'asOfDate' => $asOfDate,
            'isBalanced' => abs($totalAssets - ($totalLiabilities + $totalEquity + $retainedEarnings)) < 0.01,
        ]);
    }

    /**
     * Get accounts by type with calculated balances
     */
    private function getAccountsByType($type, $asOfDate)
    {
        return Account::where('type', $type)
            ->where('level', 2) // Get level 2 accounts (main categories)
            ->active()
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($asOfDate, $type) {
                $account->balance = $this->calculateAccountBalance($account->id, $asOfDate, $type);
                return $account;
            })
            ->filter(function ($account) {
                return $account->balance != 0; // Only show accounts with balance
            });
    }

    /**
     * Calculate account balance up to a date
     */
    private function calculateAccountBalance($accountId, $asOfDate, $type)
    {
        $debit = JournalEntry::where('account_id', $accountId)
            ->whereHas('journal', function ($q) use ($asOfDate) {
                $q->where('date', '<=', $asOfDate);
            })
            ->sum('debit');

        $credit = JournalEntry::where('account_id', $accountId)
            ->whereHas('journal', function ($q) use ($asOfDate) {
                $q->where('date', '<=', $asOfDate);
            })
            ->sum('credit');

        // Asset: Debit normal, Liability & Equity: Credit normal
        if ($type === 'asset') {
            return $debit - $credit;
        } else {
            return $credit - $debit;
        }
    }

    /**
     * Calculate retained earnings (accumulated profit/loss)
     */
    private function calculateRetainedEarnings($asOfDate)
    {
        // Revenue (credit normal)
        $revenue = JournalEntry::whereHas('account', function ($q) {
                $q->where('type', 'revenue');
            })
            ->whereHas('journal', function ($q) use ($asOfDate) {
                $q->where('date', '<=', $asOfDate);
            })
            ->selectRaw('SUM(credit) - SUM(debit) as total')
            ->value('total') ?? 0;

        // Expense (debit normal)
        $expense = JournalEntry::whereHas('account', function ($q) {
                $q->where('type', 'expense');
            })
            ->whereHas('journal', function ($q) use ($asOfDate) {
                $q->where('date', '<=', $asOfDate);
            })
            ->selectRaw('SUM(debit) - SUM(credit) as total')
            ->value('total') ?? 0;

        return $revenue - $expense;
    }
}
