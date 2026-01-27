<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankAccountController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::when(request()->search, function ($query) {
            $query->where('bank_name', 'like', '%' . request()->search . '%')
                  ->orWhere('account_name', 'like', '%' . request()->search . '%')
                  ->orWhere('account_number', 'like', '%' . request()->search . '%');
        })->latest()->paginate(10);

        return Inertia::render('Dashboard/Utilities/BankAccounts/Index', [
            'bankAccounts' => $bankAccounts,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_name'      => 'required|string|max:255',
            'account_name'   => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'is_active'      => 'required|boolean',
        ]);

        BankAccount::create($request->all());

        return back()->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $request->validate([
            'bank_name'      => 'required|string|max:255',
            'account_name'   => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'is_active'      => 'required|boolean',
        ]);

        $bankAccount->update($request->all());

        return back()->with('success', 'Rekening berhasil diperbarui.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }
}
