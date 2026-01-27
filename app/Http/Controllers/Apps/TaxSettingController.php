<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Models\TaxSetting;
use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxSettingController extends Controller
{
    /**
     * Display tax settings page
     */
    public function index()
    {
        $settings = TaxSetting::getSettings();
        $taxes = Tax::active()->get();
        
        // Get tax liability accounts
        $accounts = Account::where('type', 'liability')
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/TaxSettings/Index', [
            'settings' => $settings,
            'taxes' => $taxes,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Update tax settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'tax_enabled' => 'required|boolean',
            'default_tax_id' => 'nullable|exists:taxes,id',
            'show_tax_on_receipt' => 'required|boolean',
        ]);

        $settings = TaxSetting::getSettings();
        $settings->update($validated);

        return redirect()->route('tax-settings.index')->with('success', 'Pengaturan pajak berhasil disimpan.');
    }
}
