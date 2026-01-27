<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use App\Models\FixedAssetDepreciation;
use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class FixedAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $assets = FixedAsset::with(['assetAccount', 'depreciationAccount', 'accumulatedDepreciationAccount'])
            ->when($request->q, function ($query, $q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('code', 'like', '%' . $q . '%');
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        // Get summary statistics
        $totalAssets = FixedAsset::where('status', 'active')->sum('acquisition_cost');
        $totalBookValue = FixedAsset::where('status', 'active')->sum('book_value');
        $totalDepreciation = FixedAsset::where('status', 'active')->sum('accumulated_depreciation');

        // Get cash/bank accounts for finalize modal
        $cashAccounts = Account::where('type', 'asset')
            ->where(function ($q) {
                $q->where('code', 'like', '1-11%')
                  ->orWhere('code', 'like', '1-12%');
            })
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/FixedAssets/Index', [
            'assets' => $assets,
            'summary' => [
                'totalAssets' => $totalAssets,
                'totalBookValue' => $totalBookValue,
                'totalDepreciation' => $totalDepreciation,
            ],
            'cashAccounts' => $cashAccounts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get asset accounts (Aset Tetap category)
        $assetAccounts = Account::where('type', 'asset')
            ->where('code', 'like', '1-2%') // Aset Tetap accounts
            ->orderBy('code')
            ->get();

        // Get expense accounts for depreciation
        $expenseAccounts = Account::where('type', 'expense')
            ->orderBy('code')
            ->get();

        // Get cash/bank accounts for payment
        $cashAccounts = Account::where('type', 'asset')
            ->where(function ($q) {
                $q->where('code', 'like', '1-11%') // Kas
                  ->orWhere('code', 'like', '1-12%'); // Bank
            })
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/FixedAssets/Create', [
            'assetAccounts' => $assetAccounts,
            'expenseAccounts' => $expenseAccounts,
            'cashAccounts' => $cashAccounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:fixed_assets,code',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'useful_life' => 'required|integer|min:1',
            'salvage_value' => 'nullable|numeric|min:0',
            'depreciation_method' => 'required|in:straight_line,declining_balance',
            'asset_account_id' => 'required|exists:accounts,id',
            'depreciation_account_id' => 'required|exists:accounts,id',
            'accumulated_depreciation_account_id' => 'required|exists:accounts,id',
            'cash_account_id' => 'required|exists:accounts,id',
        ]);

        $validated['book_value'] = $validated['acquisition_cost'];
        $validated['accumulated_depreciation'] = 0;
        $validated['salvage_value'] = $validated['salvage_value'] ?? 0;
        $validated['is_finalized'] = false;

        // Store cash_account_id temporarily in session for finalization
        session(['fixed_asset_cash_account_' . $validated['code'] => $request->cash_account_id]);

        FixedAsset::create($validated);

        return redirect()->route('fixed-assets.index')->with('success', 'Aset tetap berhasil ditambahkan sebagai draft. Klik "Finalisasi" untuk membuat jurnal.');
    }

    /**
     * Finalize asset and create acquisition journal
     */
    public function finalize(FixedAsset $fixedAsset, Request $request)
    {
        if ($fixedAsset->is_finalized) {
            return redirect()->route('fixed-assets.index')->with('error', 'Aset sudah difinalisasi sebelumnya.');
        }

        $request->validate([
            'cash_account_id' => 'required|exists:accounts,id',
        ]);

        DB::transaction(function () use ($fixedAsset, $request) {
            // Create acquisition journal
            $journal = $this->createAcquisitionJournal($fixedAsset, $request->cash_account_id);

            // Mark as finalized
            $fixedAsset->update([
                'is_finalized' => true,
                'acquisition_journal_id' => $journal->id,
            ]);
        });

        return redirect()->route('fixed-assets.index')->with('success', 'Aset tetap berhasil difinalisasi dan jurnal pembelian telah dibuat.');
    }

    /**
     * Create acquisition journal (Debit Asset, Credit Cash)
     */
    private function createAcquisitionJournal(FixedAsset $asset, $cashAccountId)
    {
        $journal = Journal::create([
            'date' => $asset->acquisition_date,
            'reference' => Journal::generateReference('FA'),
            'description' => "Pembelian aset tetap: {$asset->name} ({$asset->code})",
            'total_debit' => $asset->acquisition_cost,
            'total_credit' => $asset->acquisition_cost,
            'source_type' => FixedAsset::class,
            'source_id' => $asset->id,
            'user_id' => auth()->id(),
        ]);

        // Debit: Fixed Asset Account
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $asset->asset_account_id,
            'debit' => $asset->acquisition_cost,
            'credit' => 0,
            'description' => "Pembelian {$asset->name}",
        ]);

        // Credit: Cash/Bank Account
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $cashAccountId,
            'debit' => 0,
            'credit' => $asset->acquisition_cost,
            'description' => "Pembayaran {$asset->name}",
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FixedAsset $fixedAsset)
    {
        $assetAccounts = Account::where('type', 'asset')
            ->where('code', 'like', '1-2%')
            ->orderBy('code')
            ->get();

        $expenseAccounts = Account::where('type', 'expense')
            ->orderBy('code')
            ->get();

        $cashAccounts = Account::where('type', 'asset')
            ->where(function ($q) {
                $q->where('code', 'like', '1-11%')
                  ->orWhere('code', 'like', '1-12%');
            })
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/FixedAssets/Edit', [
            'asset' => $fixedAsset,
            'assetAccounts' => $assetAccounts,
            'expenseAccounts' => $expenseAccounts,
            'cashAccounts' => $cashAccounts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:fixed_assets,code,' . $fixedAsset->id,
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'useful_life' => 'required|integer|min:1',
            'salvage_value' => 'nullable|numeric|min:0',
            'depreciation_method' => 'required|in:straight_line,declining_balance',
            'status' => 'required|in:active,disposed,sold',
            'disposal_date' => 'nullable|date',
            'disposal_value' => 'nullable|numeric|min:0',
            'asset_account_id' => 'nullable|exists:accounts,id',
            'depreciation_account_id' => 'nullable|exists:accounts,id',
            'accumulated_depreciation_account_id' => 'nullable|exists:accounts,id',
        ]);

        $fixedAsset->update($validated);

        return redirect()->route('fixed-assets.index')->with('success', 'Aset tetap berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FixedAsset $fixedAsset)
    {
        $fixedAsset->delete();

        return redirect()->route('fixed-assets.index')->with('success', 'Aset tetap berhasil dihapus.');
    }

    /**
     * Process monthly depreciation for all active assets
     */
    public function processDepreciation(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        $periodDate = Carbon::createFromFormat('Y-m', $request->period)->startOfMonth();

        // Get all active assets
        $assets = FixedAsset::where('status', 'active')
            ->where('acquisition_date', '<=', $periodDate->endOfMonth())
            ->where('book_value', '>', 0)
            ->get();

        $processedCount = 0;
        $totalDepreciation = 0;

        DB::transaction(function () use ($assets, $periodDate, &$processedCount, &$totalDepreciation) {
            foreach ($assets as $asset) {
                // Check if depreciation already processed for this period
                $existingDepreciation = FixedAssetDepreciation::where('fixed_asset_id', $asset->id)
                    ->where('period_date', $periodDate)
                    ->exists();

                if ($existingDepreciation) {
                    continue;
                }

                // Calculate depreciation
                $monthlyDepreciation = $asset->calculateMonthlyDepreciation();

                // Don't depreciate below salvage value
                $maxDepreciation = $asset->book_value - $asset->salvage_value;
                $depreciationAmount = min($monthlyDepreciation, $maxDepreciation);

                if ($depreciationAmount <= 0) {
                    continue;
                }

                // Update asset
                $asset->accumulated_depreciation += $depreciationAmount;
                $asset->book_value = $asset->acquisition_cost - $asset->accumulated_depreciation;
                $asset->save();

                // Create depreciation journal
                $journal = $this->createDepreciationJournal($asset, $depreciationAmount, $periodDate);

                // Record depreciation
                FixedAssetDepreciation::create([
                    'fixed_asset_id' => $asset->id,
                    'period_date' => $periodDate,
                    'depreciation_amount' => $depreciationAmount,
                    'accumulated_depreciation' => $asset->accumulated_depreciation,
                    'book_value' => $asset->book_value,
                    'journal_id' => $journal->id,
                ]);

                $processedCount++;
                $totalDepreciation += $depreciationAmount;
            }
        });

        return redirect()->route('fixed-assets.index')->with('success', 
            "Penyusutan bulan {$request->period} berhasil diproses. {$processedCount} aset diproses dengan total penyusutan Rp " . number_format($totalDepreciation, 0, ',', '.'));
    }

    /**
     * Create depreciation journal (Debit Expense, Credit Accumulated Depreciation)
     */
    private function createDepreciationJournal(FixedAsset $asset, $amount, $periodDate)
    {
        $journal = Journal::create([
            'date' => $periodDate->endOfMonth(),
            'reference' => Journal::generateReference('DEP'),
            'description' => "Penyusutan aset: {$asset->name} ({$asset->code}) - {$periodDate->format('F Y')}",
            'total_debit' => $amount,
            'total_credit' => $amount,
            'source_type' => FixedAssetDepreciation::class,
            'source_id' => $asset->id,
            'user_id' => auth()->id(),
        ]);

        // Debit: Depreciation Expense Account
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $asset->depreciation_account_id,
            'debit' => $amount,
            'credit' => 0,
            'description' => "Beban penyusutan {$asset->name}",
        ]);

        // Credit: Accumulated Depreciation Account
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $asset->accumulated_depreciation_account_id,
            'debit' => 0,
            'credit' => $amount,
            'description' => "Akumulasi penyusutan {$asset->name}",
        ]);

        return $journal;
    }

    /**
     * Show depreciation history
     */
    public function depreciationHistory(FixedAsset $fixedAsset)
    {
        $depreciations = $fixedAsset->depreciations()
            ->with('journal')
            ->orderBy('period_date', 'desc')
            ->get();

        return Inertia::render('Dashboard/FixedAssets/DepreciationHistory', [
            'asset' => $fixedAsset,
            'depreciations' => $depreciations,
        ]);
    }
}
