<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::when($request->q, function ($query, $q) {
            $query->where('name', 'like', '%' . $q . '%');
        })
        ->latest()
        ->paginate(10);

        return Inertia::render('Dashboard/Suppliers/Index', [
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Suppliers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
        ]);

        Supplier::create([
            'name'    => $request->name,
            'address' => $request->address,
            'phone'   => $request->phone,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return Inertia::render('Dashboard/Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
        ]);

        $supplier->update([
            'name'    => $request->name,
            'address' => $request->address,
            'phone'   => $request->phone,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
