<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountSettingController extends Controller
{
    /**
     * Display account settings.
     */
    public function index()
    {
        $settings = AccountSetting::with('account')->get();
        $accounts = Account::active()->orderBy('code')->get();

        return Inertia::render('Dashboard/Accounts/Settings', [
            'settings' => $settings,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Update account settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.account_id' => 'nullable|exists:accounts,id',
        ]);

        foreach ($request->settings as $setting) {
            AccountSetting::where('key', $setting['key'])
                ->update(['account_id' => $setting['account_id']]);
        }

        return redirect()->back()->with('success', 'Setting akun berhasil disimpan.');
    }
}
