<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\MasterAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterAddressController extends Controller
{
    public function index()
    {
        $masterAddresses = MasterAddress::when(request()->search, function ($query) {
            $query->where('province', 'like', '%' . request()->search . '%')
                  ->orWhere('city', 'like', '%' . request()->search . '%')
                  ->orWhere('district', 'like', '%' . request()->search . '%');
        })->latest()->paginate(10);

        return Inertia::render('Dashboard/Utilities/MasterAddresses/Index', [
            'masterAddresses' => $masterAddresses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'province'    => 'required|string|max:255',
            'city'        => 'required|string|max:255',
            'district'    => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:10',
        ]);

        MasterAddress::create($request->all());

        return back()->with('success', 'Master alamat berhasil ditambahkan.');
    }

    public function update(Request $request, MasterAddress $masterAddress)
    {
        $request->validate([
            'province'    => 'required|string|max:255',
            'city'        => 'required|string|max:255',
            'district'    => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:10',
        ]);

        $masterAddress->update($request->all());

        return back()->with('success', 'Master alamat berhasil diperbarui.');
    }

    public function destroy(MasterAddress $masterAddress)
    {
        $masterAddress->delete();

        return back()->with('success', 'Master alamat berhasil dihapus.');
    }
}
