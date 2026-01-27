<?php

namespace App\Services;

use App\Models\AccountSetting;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class JournalService
{
    /**
     * Create journal entries for a finalized sale
     */
    public static function createSaleJournal(Sale $sale)
    {
        // Get default accounts
        $salesAccountId = AccountSetting::getAccountId('sales');
        $cogsAccountId = AccountSetting::getAccountId('cogs');
        $inventoryAccountId = AccountSetting::getAccountId('inventory');
        $discountAccountId = AccountSetting::getAccountId('sales_discount');
        
        // Get specific accounts for cash vs credit sales
        $salesCashAccountId = AccountSetting::getAccountId('sales_cash');
        $salesCreditAccountId = AccountSetting::getAccountId('sales_credit');
        
        // Fallback to cash/receivable if not set
        if (!$salesCashAccountId) {
            $salesCashAccountId = AccountSetting::getAccountId('cash');
        }
        if (!$salesCreditAccountId) {
            $salesCreditAccountId = AccountSetting::getAccountId('accounts_receivable');
        }

        // Determine cash/receivable based on payment
        $isCashSale = $sale->pay >= $sale->grand_total;
        $assetAccountId = $isCashSale ? $salesCashAccountId : $salesCreditAccountId;

        return DB::transaction(function () use ($sale, $salesAccountId, $cogsAccountId, $inventoryAccountId, $assetAccountId, $discountAccountId) {
            // Create journal header
            $journal = Journal::create([
                'date' => $sale->created_at->toDateString(),
                'reference' => Journal::generateReference('SALE'),
                'description' => "Penjualan {$sale->invoice}",
                'source_type' => Sale::class,
                'source_id' => $sale->id,
                'user_id' => auth()->id(),
            ]);

            $entries = [];

            // Entry 1: Debit Cash/Receivable
            if ($assetAccountId) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'account_id' => $assetAccountId,
                    'debit' => $sale->grand_total,
                    'credit' => 0,
                    'description' => 'Penerimaan dari penjualan',
                ];
            }

            // Entry 2: Credit Sales
            if ($salesAccountId) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'account_id' => $salesAccountId,
                    'debit' => 0,
                    'credit' => $sale->total,
                    'description' => 'Pendapatan penjualan',
                ];
            }

            // Entry 3: Debit Discount (if any)
            if ($sale->discount > 0 && $discountAccountId) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'account_id' => $discountAccountId,
                    'debit' => $sale->discount,
                    'credit' => 0,
                    'description' => 'Diskon penjualan',
                ];
            }

            // Entry 4: Debit COGS
            $totalCogs = 0;
            foreach ($sale->details as $detail) {
                if ($detail->product_id && $detail->product) {
                    $totalCogs += $detail->product->buy_price * $detail->qty;
                } elseif ($detail->bundle_id && $detail->bundle) {
                    foreach ($detail->bundle->items as $item) {
                        $totalCogs += $item->product->buy_price * $item->qty * $detail->qty;
                    }
                }
            }

            if ($totalCogs > 0 && $cogsAccountId) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'account_id' => $cogsAccountId,
                    'debit' => $totalCogs,
                    'credit' => 0,
                    'description' => 'Harga Pokok Penjualan',
                ];
            }

            // Entry 5: Credit Inventory
            if ($totalCogs > 0 && $inventoryAccountId) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'account_id' => $inventoryAccountId,
                    'debit' => 0,
                    'credit' => $totalCogs,
                    'description' => 'Pengurangan persediaan',
                ];
            }

            // Insert all entries
            foreach ($entries as $entry) {
                JournalEntry::create($entry);
            }

            // Recalculate totals
            $journal->recalculateTotals();

            return $journal;
        });
    }
}
