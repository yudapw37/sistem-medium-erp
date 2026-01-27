<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $shippingMethods = ShippingMethod::when(request()->search, function ($query) {
            $query->where('name', 'like', '%' . request()->search . '%')
                  ->orWhere('code', 'like', '%' . request()->search . '%');
        })->latest()->paginate(10);

        return Inertia::render('Dashboard/Utilities/ShippingMethods/Index', [
            'shippingMethods' => $shippingMethods,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:255|unique:shipping_methods',
            'is_active' => 'required|boolean',
        ]);

        ShippingMethod::create($request->all());

        return back()->with('success', 'Jasa kirim berhasil ditambahkan.');
    }

    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:255|unique:shipping_methods,code,' . $shippingMethod->id,
            'is_active' => 'required|boolean',
        ]);

        $shippingMethod->update($request->all());

        return back()->with('success', 'Jasa kirim berhasil diperbarui.');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();

        return back()->with('success', 'Jasa kirim berhasil dihapus.');
    }
}
