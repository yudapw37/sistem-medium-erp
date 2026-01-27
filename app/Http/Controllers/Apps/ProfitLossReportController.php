<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProfitLossReportController extends Controller
{
    /**
     * Display profit/loss report.
     */
    public function index(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        // Get revenue accounts with totals
        $revenueAccounts = Account::where('type', 'revenue')
            ->active()
            ->with(['children' => function ($q) {
                $q->active()->orderBy('code');
            }])
            ->whereNull('parent_id')
            ->orWhere(function ($q) {
                $q->where('type', 'revenue')->where('level', 2)->active();
            })
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                $account->total = $this->getAccountBalance($account->id, $startDate, $endDate);
                $account->children = $account->children->map(function ($child) use ($startDate, $endDate) {
                    $child->total = $this->getAccountBalance($child->id, $startDate, $endDate);
                    return $child;
                });
                return $account;
            });

        // Get expense accounts with totals
        $expenseAccounts = Account::where('type', 'expense')
            ->active()
            ->with(['children' => function ($q) {
                $q->active()->orderBy('code');
            }])
            ->whereNull('parent_id')
            ->orWhere(function ($q) {
                $q->where('type', 'expense')->where('level', 2)->active();
            })
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                $account->total = $this->getAccountBalance($account->id, $startDate, $endDate);
                $account->children = $account->children->map(function ($child) use ($startDate, $endDate) {
                    $child->total = $this->getAccountBalance($child->id, $startDate, $endDate);
                    return $child;
                });
                return $account;
            });

        $totalRevenue = JournalEntry::whereHas('account', function ($q) {
                $q->where('type', 'revenue');
            })
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('credit') - JournalEntry::whereHas('account', function ($q) {
                $q->where('type', 'revenue');
            })
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('debit');

        $totalExpense = JournalEntry::whereHas('account', function ($q) {
                $q->where('type', 'expense');
            })
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('debit') - JournalEntry::whereHas('account', function ($q) {
                $q->where('type', 'expense');
            })
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('credit');

        $profitLoss = $totalRevenue - $totalExpense;

        return Inertia::render('Dashboard/Reports/ProfitLoss', [
            'revenueAccounts' => $revenueAccounts,
            'expenseAccounts' => $expenseAccounts,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'profitLoss' => $profitLoss,
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Get account balance for period
     */
    private function getAccountBalance($accountId, $startDate, $endDate)
    {
        $account = Account::find($accountId);
        if (!$account) return 0;

        $debit = JournalEntry::where('account_id', $accountId)
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('debit');

        $credit = JournalEntry::where('account_id', $accountId)
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('credit');

        // Revenue: Credit - Debit, Expense: Debit - Credit
        if ($account->type === 'revenue') {
            return $credit - $debit;
        } else {
            return $debit - $credit;
        }
    }
}
