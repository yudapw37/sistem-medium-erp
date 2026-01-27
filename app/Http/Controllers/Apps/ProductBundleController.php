<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductBundleItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductBundleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bundles = ProductBundle::with('items.product')
            ->when($request->q, function ($query, $q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('code', 'like', '%' . $q . '%');
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/ProductBundles/Index', [
            'bundles' => $bundles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/ProductBundles/Create', [
            'products' => Product::select('id', 'title', 'barcode', 'sell_price', 'weight')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:product_bundles,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sell_price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $bundle = ProductBundle::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sell_price' => $validated['sell_price'],
            'image' => $validated['image'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        foreach ($validated['items'] as $item) {
            ProductBundleItem::create([
                'bundle_id' => $bundle->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
            ]);
        }

        // Calculate and save total weight
        $bundle->load('items.product');
        $bundle->update(['weight' => $bundle->calculated_weight]);

        return redirect()->route('product-bundles.index')
            ->with('success', 'Bundle berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bundle = ProductBundle::with(['items.product'])->findOrFail($id);

        return Inertia::render('Dashboard/ProductBundles/Detail', [
            'bundle' => $bundle,
            'availableStock' => $bundle->available_stock,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bundle = ProductBundle::with('items.product')->findOrFail($id);

        return Inertia::render('Dashboard/ProductBundles/Edit', [
            'bundle' => $bundle,
            'products' => Product::select('id', 'title', 'barcode', 'sell_price', 'weight')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bundle = ProductBundle::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|unique:product_bundles,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sell_price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $bundle->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sell_price' => $validated['sell_price'],
            'image' => $validated['image'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Delete old items and create new ones
        $bundle->items()->delete();

        foreach ($validated['items'] as $item) {
            ProductBundleItem::create([
                'bundle_id' => $bundle->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
            ]);
        }

        // Calculate and save total weight
        $bundle->load('items.product');
        $bundle->update(['weight' => $bundle->calculated_weight]);

        return redirect()->route('product-bundles.index')
            ->with('success', 'Bundle berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bundle = ProductBundle::findOrFail($id);

        // Check if bundle has been used in sales
        if ($bundle->sales()->count() > 0) {
            return back()->with('error', 'Bundle tidak bisa dihapus karena sudah digunakan dalam transaksi!');
        }

        $bundle->delete();

        return redirect()->route('product-bundles.index')
            ->with('success', 'Bundle berhasil dihapus!');
    }

    /**
     * Search products for bundle creation
     */
    public function searchProduct(Request $request)
    {
        $query = $request->input('q');

        $products = Product::where(function ($q) use ($query) {
            $q->where('title', 'like', '%' . $query . '%')
                ->orWhere('barcode', 'like', '%' . $query . '%');
        })
            ->select('id', 'title', 'barcode', 'sell_price')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    /**
     * Check bundle stock availability
     */
    public function checkStock(string $id)
    {
        $bundle = ProductBundle::with('items.product')->findOrFail($id);

        return response()->json([
            'available_stock' => $bundle->available_stock,
            'items' => $bundle->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->title,
                    'required_qty' => $item->qty,
                    'available_stock' => $item->product->stock ?? 0,
                ];
            }),
        ]);
    }
}
