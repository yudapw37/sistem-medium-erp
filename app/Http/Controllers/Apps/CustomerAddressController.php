<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = CustomerAddress::with('customer')
            ->when(request()->search, function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . request()->search . '%');
                })->orWhere('name', 'like', '%' . request()->search . '%');
            })
            ->latest()->paginate(10);

        $customers = Customer::orderBy('name')->get();

        return Inertia::render('Dashboard/Customers/Addresses/Index', [
            'addresses' => $addresses,
            'customers' => $customers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'label'       => 'required|string|max:255',
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:255',
            'address'     => 'required|string',
        ]);

        // Check if customer already has 3 addresses
        $count = CustomerAddress::where('customer_id', $request->customer_id)->count();
        if ($count >= 3) {
            return back()->withErrors(['customer_id' => 'Maksimal 3 alamat tersimpan per pelanggan.']);
        }

        CustomerAddress::create($request->all());

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerAddress $customerAddress)
    {
        $request->validate([
            'label'   => 'required|string|max:255',
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:255',
            'address' => 'required|string',
        ]);

        $customerAddress->update($request->all());

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerAddress $customerAddress)
    {
        $customerAddress->delete();

        return back()->with('success', 'Alamat berhasil dihapus.');
    }
}
