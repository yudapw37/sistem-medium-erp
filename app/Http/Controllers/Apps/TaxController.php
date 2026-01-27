<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxController extends Controller
{
    /**
     * Display a listing of taxes
     */
    public function index(Request $request)
    {
        $taxes = Tax::with('account')
            ->when($request->q, function ($query, $q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('code', 'like', '%' . $q . '%');
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/Taxes/Index', [
            'taxes' => $taxes,
        ]);
    }

    /**
     * Show the form for creating a new tax
     */
    public function create()
    {
        $accounts = Account::where('type', 'liability')
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/Taxes/Create', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Store a newly created tax
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:taxes,code',
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:included,excluded',
            'applies_to' => 'required|in:sales,purchases,both',
            'account_id' => 'nullable|exists:accounts,id',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Tax::where('is_default', true)->update(['is_default' => false]);
        }

        Tax::create($validated);

        return redirect()->route('taxes.index')->with('success', 'Pajak berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a tax
     */
    public function edit(Tax $tax)
    {
        $accounts = Account::where('type', 'liability')
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/Taxes/Edit', [
            'tax' => $tax,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Update the specified tax
     */
    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:taxes,code,' . $tax->id,
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:included,excluded',
            'applies_to' => 'required|in:sales,purchases,both',
            'account_id' => 'nullable|exists:accounts,id',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Tax::where('id', '!=', $tax->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $tax->update($validated);

        return redirect()->route('taxes.index')->with('success', 'Pajak berhasil diperbarui.');
    }

    /**
     * Remove the specified tax
     */
    public function destroy(Tax $tax)
    {
        $tax->delete();

        return redirect()->route('taxes.index')->with('success', 'Pajak berhasil dihapus.');
    }
}
