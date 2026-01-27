<?php

namespace App\Http\Controllers\Apps;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get products
        $products = Product::when(request()->search, function ($products) {
            $products = $products->where('title', 'like', '%' . request()->search . '%');
        })->with('category')->latest()->paginate(20);

        //return inertia
        return Inertia::render('Dashboard/Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get categories
        $categories = Category::all();

        //return inertia
        return Inertia::render('Dashboard/Products/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**
         * validate
         */
        $request->validate([
            'barcode' => 'required|unique:products,barcode',
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'buy_price' => 'required',
            'sell_price' => 'required',
            'is_sellable' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'stock' => 'required',
        ]);
        //upload image
        $image = $request->file('image');
        $image->storeAs('public/products', $image->hashName());

        //create product
        $product = Product::create([
            'image' => $image->hashName(),
            'barcode' => $request->barcode,
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'buy_price' => $request->buy_price,
            'sell_price' => $request->sell_price,
            'is_sellable' => $request->is_sellable ?? true,
            'weight' => $request->weight ?? 0,
            'stock' => $request->stock,
        ]);

        ProductStock::create([
             'product_id'     => $product->id,
             'warehouse_id'   => $request->warehouse_id ?? (\App\Models\Warehouse::first()?->id),
             'type'           => 'in',
             'qty'            => $request->stock,
             'previous_stock' => 0,
             'current_stock'  => $request->stock,
             'user_id'        => auth()->id(),
             'note'           => 'Initial Stock',
        ]);

        if ($request->stock > 0 || $request->warehouse_id || \App\Models\Warehouse::exists()) {
            $warehouseId = $request->warehouse_id ?? \App\Models\Warehouse::first()?->id;
            if ($warehouseId) {
                \App\Models\ProductWarehouse::updateOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
                    ['stock' => $request->stock]
                );
            }
        }

        //redirect
        return to_route('products.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //get categories
        $categories = Category::all();
        
        // Get all active units
        $units = \App\Models\Unit::active()->orderBy('name')->get();
        
        // Get product units with unit relationship
        $productUnits = $product->productUnits()->with('unit')->get();

        return Inertia::render('Dashboard/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'units' => $units,
            'productUnits' => $productUnits,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        /**
         * validate
         */
        $request->validate([
            'barcode' => 'required|unique:products,barcode,' . $product->id,
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'buy_price' => 'required',
            'sell_price' => 'required',
            'is_sellable' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
        ]);

        //check image update
        $previousStock = $product->stock;

        if ($request->file('image')) {

            //remove old image
            Storage::disk('local')->delete('public/products/' . basename($product->image));

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/products', $image->hashName());

            //update product with new image
            $product->update([
                'image' => $image->hashName(),
                'barcode' => $request->barcode,
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'buy_price' => $request->buy_price,
                'sell_price' => $request->sell_price,
                'is_sellable' => $request->is_sellable,
                'weight' => $request->weight ?? 0,
                'stock' => $request->stock,
            ]);

        }

        //update product without image
        $product->update([
            'barcode' => $request->barcode,
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'buy_price' => $request->buy_price,
            'sell_price' => $request->sell_price,
            'is_sellable' => $request->is_sellable,
            'weight' => $request->weight ?? 0,
            'stock' => $request->stock,
        ]);

        if ($request->stock != $previousStock) {
            $diff = $request->stock - $previousStock;
            $type = $diff > 0 ? 'in' : 'out';
            $qty = abs($diff);

            $warehouseId = $request->warehouse_id ?? \App\Models\Warehouse::first()?->id;

            ProductStock::create([
                'product_id'     => $product->id,
                'warehouse_id'   => $warehouseId,
                'type'           => $type,
                'qty'            => $qty,
                'previous_stock' => $previousStock,
                'current_stock'  => $request->stock,
                'user_id'        => auth()->id(),
                'note'           => 'Stock Update',
            ]);

            if ($warehouseId) {
                \App\Models\ProductWarehouse::updateOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
                    ['stock' => $request->stock]
                );
            }
        }

        //redirect
        return to_route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //find by ID
        $product = Product::findOrFail($id);

        //remove image
        Storage::disk('local')->delete('public/products/' . basename($product->image));

        //delete
        $product->delete();

        //redirect
        return back();
    }

    /**
     * Sync product units configuration
     */
    public function syncUnits(Request $request, Product $product)
    {
        $request->validate([
            'units' => 'required|array',
            'units.*.unit_id' => 'required|exists:units,id',
            'units.*.conversion_rate' => 'required|numeric|min:0.0001',
            'units.*.barcode' => 'nullable|string',
            'units.*.sell_price' => 'required|numeric|min:0',
            'units.*.buy_price' => 'required|numeric|min:0',
            'units.*.is_base' => 'boolean',
            'units.*.is_default' => 'boolean',
        ]);

        // Delete existing product units
        $product->productUnits()->delete();

        // Create new ones
        foreach ($request->units as $unitData) {
            // Ensure only one base unit
            if (!empty($unitData['is_base'])) {
                \App\Models\ProductUnit::where('product_id', $product->id)
                    ->where('is_base', true)
                    ->update(['is_base' => false]);
            }

            $product->productUnits()->create([
                'unit_id' => $unitData['unit_id'],
                'conversion_rate' => $unitData['conversion_rate'],
                'barcode' => $unitData['barcode'] ?? null,
                'sell_price' => $unitData['sell_price'],
                'buy_price' => $unitData['buy_price'],
                'is_base' => $unitData['is_base'] ?? false,
                'is_default' => $unitData['is_default'] ?? false,
            ]);
        }

        return back()->with('success', 'Satuan produk berhasil disimpan.');
    }
}
