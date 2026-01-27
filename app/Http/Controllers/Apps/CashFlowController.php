<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashFlowController extends Controller
{
    /**
     * Display cash flow statement.
     */
    public function index(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        // Get cash account IDs (Kas & Bank)
        $cashAccountIds = Account::whereIn('code', ['1-1100', '1-1200'])
            ->orWhere('name', 'like', '%Kas%')
            ->orWhere('name', 'like', '%Bank%')
            ->where('type', 'asset')
            ->pluck('id')
            ->toArray();

        // Opening Cash Balance
        $openingBalance = $this->getCashBalance($cashAccountIds, null, date('Y-m-d', strtotime($startDate . ' -1 day')));

        // === ARUS KAS OPERASIONAL ===
        $operatingActivities = [];

        // Penerimaan dari Penjualan (credit to revenue accounts from cash)
        $salesReceipts = $this->getCashFlowFromType('revenue', $cashAccountIds, $startDate, $endDate, 'inflow');
        if ($salesReceipts != 0) {
            $operatingActivities[] = ['name' => 'Penerimaan dari Penjualan', 'amount' => $salesReceipts];
        }

        // Pembayaran untuk Pembelian (HPP/COGS)
        $purchasePayments = $this->getCashFlowFromType('expense', $cashAccountIds, $startDate, $endDate, 'outflow');
        if ($purchasePayments != 0) {
            $operatingActivities[] = ['name' => 'Pembayaran untuk Beban Operasional', 'amount' => -$purchasePayments];
        }

        $totalOperating = collect($operatingActivities)->sum('amount');

        // === ARUS KAS INVESTASI ===
        $investingActivities = [];

        // Pembelian Aset Tetap (debit to fixed asset accounts)
        $assetPurchases = $this->getAssetTransactions($cashAccountIds, $startDate, $endDate, 'purchase');
        if ($assetPurchases != 0) {
            $investingActivities[] = ['name' => 'Pembelian Aset Tetap', 'amount' => -$assetPurchases];
        }

        // Penjualan Aset Tetap
        $assetSales = $this->getAssetTransactions($cashAccountIds, $startDate, $endDate, 'sale');
        if ($assetSales != 0) {
            $investingActivities[] = ['name' => 'Penjualan Aset Tetap', 'amount' => $assetSales];
        }

        $totalInvesting = collect($investingActivities)->sum('amount');

        // === ARUS KAS PENDANAAN ===
        $financingActivities = [];

        // Penerimaan Pinjaman (credit to liability from cash)
        $loanReceipts = $this->getLiabilityTransactions($cashAccountIds, $startDate, $endDate, 'increase');
        if ($loanReceipts != 0) {
            $financingActivities[] = ['name' => 'Penerimaan Pinjaman', 'amount' => $loanReceipts];
        }

        // Pembayaran Pinjaman
        $loanPayments = $this->getLiabilityTransactions($cashAccountIds, $startDate, $endDate, 'decrease');
        if ($loanPayments != 0) {
            $financingActivities[] = ['name' => 'Pembayaran Pinjaman', 'amount' => -$loanPayments];
        }

        // Setoran Modal
        $capitalInjection = $this->getEquityTransactions($cashAccountIds, $startDate, $endDate, 'increase');
        if ($capitalInjection != 0) {
            $financingActivities[] = ['name' => 'Setoran Modal', 'amount' => $capitalInjection];
        }

        // Penarikan Modal/Dividen
        $capitalWithdrawal = $this->getEquityTransactions($cashAccountIds, $startDate, $endDate, 'decrease');
        if ($capitalWithdrawal != 0) {
            $financingActivities[] = ['name' => 'Penarikan Modal', 'amount' => -$capitalWithdrawal];
        }

        $totalFinancing = collect($financingActivities)->sum('amount');

        // Net Cash Flow
        $netCashFlow = $totalOperating + $totalInvesting + $totalFinancing;
        $closingBalance = $openingBalance + $netCashFlow;

        return Inertia::render('Dashboard/Reports/CashFlow', [
            'operatingActivities' => $operatingActivities,
            'investingActivities' => $investingActivities,
            'financingActivities' => $financingActivities,
            'totalOperating' => $totalOperating,
            'totalInvesting' => $totalInvesting,
            'totalFinancing' => $totalFinancing,
            'netCashFlow' => $netCashFlow,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'month' => $month,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Get cash balance up to a date
     */
    private function getCashBalance($cashAccountIds, $startDate = null, $endDate = null)
    {
        $query = JournalEntry::whereIn('account_id', $cashAccountIds)
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                if ($endDate) $q->where('date', '<=', $endDate);
                if ($startDate) $q->where('date', '>=', $startDate);
            });

        $debit = (clone $query)->sum('debit');
        $credit = (clone $query)->sum('credit');

        return $debit - $credit; // Cash is asset (debit normal)
    }

    /**
     * Get cash flow from revenue/expense accounts
     */
    private function getCashFlowFromType($type, $cashAccountIds, $startDate, $endDate, $direction)
    {
        // Get journals that affect cash accounts
        $journalIds = JournalEntry::whereIn('account_id', $cashAccountIds)
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->pluck('journal_id');

        // Sum entries for the type in those journals
        $query = JournalEntry::whereIn('journal_id', $journalIds)
            ->whereHas('account', function ($q) use ($type) {
                $q->where('type', $type);
            });

        if ($direction === 'inflow') {
            return $query->sum('credit') - $query->sum('debit');
        } else {
            return $query->sum('debit') - $query->sum('credit');
        }
    }

    /**
     * Get asset transactions (for investing activities)
     */
    private function getAssetTransactions($cashAccountIds, $startDate, $endDate, $type)
    {
        // Fixed assets typically have codes starting with 1-2xxx or contain 'Tetap'
        $fixedAssetIds = Account::where('type', 'asset')
            ->where(function ($q) {
                $q->where('code', 'like', '1-2%')
                    ->orWhere('name', 'like', '%Tetap%')
                    ->orWhere('name', 'like', '%Peralatan%')
                    ->orWhere('name', 'like', '%Kendaraan%');
            })
            ->pluck('id');

        $journalIds = JournalEntry::whereIn('account_id', $cashAccountIds)
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->pluck('journal_id');

        $query = JournalEntry::whereIn('journal_id', $journalIds)
            ->whereIn('account_id', $fixedAssetIds);

        if ($type === 'purchase') {
            return $query->sum('debit');
        } else {
            return $query->sum('credit');
        }
    }

    /**
     * Get liability transactions (for financing activities)
     */
    private function getLiabilityTransactions($cashAccountIds, $startDate, $endDate, $type)
    {
        $liabilityIds = Account::where('type', 'liability')->pluck('id');

        $journalIds = JournalEntry::whereIn('account_id', $cashAccountIds)
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->pluck('journal_id');

        $query = JournalEntry::whereIn('journal_id', $journalIds)
            ->whereIn('account_id', $liabilityIds);

        if ($type === 'increase') {
            return $query->sum('credit') - $query->sum('debit');
        } else {
            return $query->sum('debit');
        }
    }

    /**
     * Get equity transactions (for financing activities)
     */
    private function getEquityTransactions($cashAccountIds, $startDate, $endDate, $type)
    {
        $equityIds = Account::where('type', 'equity')->pluck('id');

        $journalIds = JournalEntry::whereIn('account_id', $cashAccountIds)
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->pluck('journal_id');

        $query = JournalEntry::whereIn('journal_id', $journalIds)
            ->whereIn('account_id', $equityIds);

        if ($type === 'increase') {
            return $query->sum('credit');
        } else {
            return $query->sum('debit');
        }
    }
}
