<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneralLedgerController extends Controller
{
    /**
     * Display general ledger.
     */
    public function index(Request $request)
    {
        $accounts = Account::active()->orderBy('code')->get();
        
        $selectedAccount = null;
        $entries = collect();
        $openingBalance = 0;
        
        if ($request->account_id) {
            $selectedAccount = Account::find($request->account_id);
            
            $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $request->end_date ?? now()->format('Y-m-d');
            
            // Calculate opening balance (before start date)
            $openingBalance = $this->calculateBalance(
                $request->account_id, 
                null, 
                date('Y-m-d', strtotime($startDate . ' -1 day'))
            );
            
            // Get entries for the period
            $entries = JournalEntry::with(['journal'])
                ->where('account_id', $request->account_id)
                ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                })
                ->orderBy('created_at')
                ->get()
                ->map(function ($entry) use (&$openingBalance, $selectedAccount) {
                    // Calculate running balance
                    if (in_array($selectedAccount->type, ['asset', 'expense'])) {
                        // Debit normal balance
                        $openingBalance += $entry->debit - $entry->credit;
                    } else {
                        // Credit normal balance
                        $openingBalance += $entry->credit - $entry->debit;
                    }
                    $entry->balance = $openingBalance;
                    return $entry;
                });
        }

        return Inertia::render('Dashboard/Reports/GeneralLedger', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'entries' => $entries,
            'openingBalance' => $request->account_id ? $this->calculateBalance(
                $request->account_id, 
                null, 
                date('Y-m-d', strtotime(($request->start_date ?? now()->startOfMonth()->format('Y-m-d')) . ' -1 day'))
            ) : 0,
            'filters' => $request->only(['account_id', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Calculate account balance up to a date
     */
    private function calculateBalance($accountId, $startDate = null, $endDate = null)
    {
        $account = Account::find($accountId);
        if (!$account) return 0;

        $query = JournalEntry::where('account_id', $accountId)
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                if ($endDate) {
                    $q->where('date', '<=', $endDate);
                }
                if ($startDate) {
                    $q->where('date', '>=', $startDate);
                }
            });

        $debit = (clone $query)->sum('debit');
        $credit = (clone $query)->sum('credit');

        // Asset & Expense: Debit normal
        // Liability, Equity, Revenue: Credit normal
        if (in_array($account->type, ['asset', 'expense'])) {
            return $debit - $credit;
        } else {
            return $credit - $debit;
        }
    }
}
