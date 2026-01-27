<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts.
     */
    public function index(Request $request)
    {
        $accounts = Account::with('children.children')
            ->roots()
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->q, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('code', 'like', '%' . $request->q . '%')
                      ->orWhere('name', 'like', '%' . $request->q . '%');
                });
            })
            ->orderBy('code')
            ->get();

        $allAccounts = Account::active()->orderBy('code')->get();

        return Inertia::render('Dashboard/Accounts/Index', [
            'accounts' => $accounts,
            'allAccounts' => $allAccounts,
            'filters' => $request->only(['type', 'q']),
        ]);
    }

    /**
     * Show the form for creating a new account.
     */
    public function create()
    {
        $parentAccounts = Account::active()
            ->where('level', '<', 3)
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/Accounts/Create', [
            'parentAccounts' => $parentAccounts,
        ]);
    }

    /**
     * Store a newly created account.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
        ]);

        $level = 1;
        if ($request->parent_id) {
            $parent = Account::find($request->parent_id);
            $level = $parent->level + 1;
        }

        Account::create([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'parent_id' => $request->parent_id,
            'level' => $level,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    /**
     * Show the form for editing an account.
     */
    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $parentAccounts = Account::active()
            ->where('level', '<', 3)
            ->where('id', '!=', $id)
            ->orderBy('code')
            ->get();

        return Inertia::render('Dashboard/Accounts/Edit', [
            'account' => $account,
            'parentAccounts' => $parentAccounts,
        ]);
    }

    /**
     * Update the specified account.
     */
    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:10|unique:accounts,code,' . $id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $level = 1;
        if ($request->parent_id) {
            $parent = Account::find($request->parent_id);
            $level = $parent->level + 1;
        }

        $account->update([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'parent_id' => $request->parent_id,
            'level' => $level,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Remove the specified account.
     */
    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        
        if ($account->children()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun yang memiliki sub-akun.');
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil dihapus.');
    }
}
