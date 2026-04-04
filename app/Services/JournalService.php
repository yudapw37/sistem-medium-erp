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

    /**
     * Create journal entries for a finalized Sale Return
     */
    public static function createSaleReturnJournal(\App\Models\SaleReturn $return)
    {
        // Get default accounts
        $salesAccountId = AccountSetting::getAccountId('sales');
        $cogsAccountId = AccountSetting::getAccountId('cogs');
        $inventoryAccountId = AccountSetting::getAccountId('inventory');
        
        $assetAccountId = null;
        if ($return->Sale) {
            $isCashSale = $return->Sale->payment_type !== 'tempo';
            
            if ($isCashSale) {
                $assetAccountId = AccountSetting::getAccountId('sales_cash') ?: AccountSetting::getAccountId('cash');
            } else {
                $assetAccountId = AccountSetting::getAccountId('accounts_receivable');
            }
        } else {
            // Default to Cash if no specific invoice linked
            $assetAccountId = AccountSetting::getAccountId('cash');
        }

        return DB::transaction(function () use ($return, $salesAccountId, $cogsAccountId, $inventoryAccountId, $assetAccountId) {
            $journal = Journal::create([
                'date' => $return->date->toDateString(),
                'reference' => Journal::generateReference('RTR'),
                'description' => "Retur Penjualan {$return->code}",
                'source_type' => \App\Models\SaleReturn::class,
                'source_id' => $return->id,
                'user_id' => auth()->id(),
            ]);

            $entries = [];

            // Entry 1: Debit Sales (Reverse Revenue)
            if ($salesAccountId) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'account_id' => $salesAccountId,
                    'debit' => $return->grand_total,
                    'credit' => 0,
                    'description' => 'Retur Pendapatan Penjualan',
                ];
            }

            // Entry 2: Credit Cash/Receivable
            if ($assetAccountId) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'account_id' => $assetAccountId,
                    'debit' => 0,
                    'credit' => $return->grand_total,
                    'description' => 'Pengurangan Piutang/Kas atas Retur',
                ];
            }

            // Entry 3: Debit Inventory
            $totalCogs = 0;
            foreach ($return->details as $detail) {
                if ($detail->product) {
                    $totalCogs += $detail->product->buy_price * $detail->qty;
                }
            }

            if ($totalCogs > 0 && $inventoryAccountId) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'account_id' => $inventoryAccountId,
                    'debit' => $totalCogs,
                    'credit' => 0,
                    'description' => 'Barang masuk dari retur',
                ];
            }

            // Entry 4: Credit COGS
            if ($totalCogs > 0 && $cogsAccountId) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'account_id' => $cogsAccountId,
                    'debit' => 0,
                    'credit' => $totalCogs,
                    'description' => 'Pengurangan Harga Pokok Penjualan',
                ];
            }

            foreach ($entries as $entry) {
                JournalEntry::create($entry);
            }

            $journal->recalculateTotals();

            return $journal;
        });
    }

    /**
     * Create journal entries for a finalized Purchase Return.
     * Reverses the original purchase journal:
     * - Dr. Hutang Usaha/Kas  (reverse the credit from original purchase)
     * - Cr. Inventory         (reverse the debit from original purchase)
     */
    public static function createPurchaseReturnJournal(\App\Models\PurchaseReturn $return)
    {
        $inventoryAccountId = AccountSetting::getAccountId('inventory');
        $cashAccountId      = AccountSetting::getAccountId('cash');
        $apAccountId        = AccountSetting::getAccountId('accounts_payable');

        // Determine which account to reverse based on original purchase type
        $assetAccountId = null;
        if ($return->Purchase) {
            $isCreditPurchase = in_array($return->Purchase->payment_type, ['tempo', 'credit']);
            $assetAccountId   = $isCreditPurchase ? $apAccountId : $cashAccountId;
        } else {
            $assetAccountId = $cashAccountId;
        }

        return DB::transaction(function () use ($return, $inventoryAccountId, $assetAccountId) {
            $journal = Journal::create([
                'date'        => $return->date->toDateString(),
                'reference'   => Journal::generateReference('RTUR'),
                'description' => "Retur Pembelian {$return->code}",
                'source_type' => \App\Models\PurchaseReturn::class,
                'source_id'   => $return->id,
                'user_id'     => auth()->id(),
            ]);

            $entries = [];

            // Dr. Hutang/Kas — reverse the credit from original purchase
            if ($assetAccountId) {
                $entries[] = [
                    'journal_id'  => $journal->id,
                    'account_id'  => $assetAccountId,
                    'debit'       => $return->grand_total,
                    'credit'      => 0,
                    'description' => 'Pengurangan Hutang/Kas akibat Retur Pembelian',
                ];
            }

            // Cr. Inventory — reverse the debit from original purchase
            if ($inventoryAccountId) {
                $entries[] = [
                    'journal_id'  => $journal->id,
                    'account_id'  => $inventoryAccountId,
                    'debit'       => 0,
                    'credit'      => $return->grand_total,
                    'description' => 'Pengurangan persediaan akibat Retur Pembelian',
                ];
            }

            foreach ($entries as $entry) {
                JournalEntry::create($entry);
            }

            $journal->recalculateTotals();

            return $journal;
        });
    }
}
