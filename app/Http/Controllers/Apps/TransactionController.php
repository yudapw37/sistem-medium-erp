<?php
namespace App\Http\Controllers\Apps;

use App\Exceptions\PaymentGatewayException;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\Transaction;
use App\Models\Warehouse;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\TaxHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index(Request $request)
    {
        $userId = auth()->user()->id;

        // Get active cart items (not held)
        $carts = Cart::with('product')
            ->where('cashier_id', $userId)
            ->active()
            ->latest()
            ->get();

        // Get held carts grouped by hold_id
        $heldCarts = Cart::with('product:id,title,sell_price,image')
            ->where('cashier_id', $userId)
            ->held()
            ->get()
            ->groupBy('hold_id')
            ->map(function ($items, $holdId) {
                $first = $items->first();
                return [
                    'hold_id'     => $holdId,
                    'label'       => $first->hold_label,
                    'held_at'     => $first->held_at?->toISOString(),
                    'items_count' => $items->sum('qty'),
                    'total'       => $items->sum('price'),
                ];
            })
            ->values();

        //get all customers
        $customers = Customer::latest()->get();

        // get all warehouses
        $warehouses = Warehouse::all();

        // Check for warehouse_id parameter
        $warehouseId = $request->warehouse_id;

        if ($warehouseId) {
            // Get products with stock > 0 in selected warehouse
            $products = Product::with('category:id,name')
                ->join('product_warehouses', 'products.id', '=', 'product_warehouses.product_id')
                ->where('product_warehouses.warehouse_id', $warehouseId)
                ->where('products.is_sellable', true)
                ->where('products.is_sellable', true)
                ->select(
                    'products.id', 
                    'products.barcode', 
                    'products.title', 
                    'products.description', 
                    'products.image', 
                    'products.buy_price', 
                    'products.sell_price', 
                    'products.category_id',
                    'product_warehouses.stock'
                )
                ->orderBy('products.title')
                ->get()
                ->map(function($product) {
                    $product->type = 'product';
                    return $product;
                });

            // Get active bundles
            $bundles = ProductBundle::with('items.product')
                ->active()
                ->get()
                ->map(function($bundle) use ($warehouseId) {
                    $stock = $bundle->getStockForWarehouse($warehouseId);
                    return [
                        'id' => $bundle->id,
                        'barcode' => $bundle->code,
                        'title' => '[BUNDLE] ' . $bundle->name,
                        'description' => $bundle->description,
                        'image' => $bundle->image,
                        'sell_price' => $bundle->sell_price,
                        'stock' => $stock,
                        'type' => 'bundle',
                        'category_id' => null, // Bundles don't have a category
                    ];
                });

            $products = $products->concat($bundles);
        } else {
            // If no warehouse selected, return empty product list to force selection
            $products = collect([]); 
        }

        // get all categories
        $categories = \App\Models\Category::select('id', 'name', 'image')
            ->orderBy('name')
            ->get();

        $paymentSetting = PaymentSetting::first();

        $carts_total = 0;
        foreach ($carts as $cart) {
            $carts_total += $cart->price;
        }

        $defaultGateway = $paymentSetting?->default_gateway ?? 'cash';
        if (
            $defaultGateway !== 'cash'
            && (! $paymentSetting || ! $paymentSetting->isGatewayReady($defaultGateway))
        ) {
            $defaultGateway = 'cash';
        }

        return Inertia::render('Dashboard/Transactions/Index', [
            'carts'                 => $carts,
            'carts_total'           => $carts_total,
            'heldCarts'             => $heldCarts,
            'customers'             => $customers,
            'products'              => $products,
            'categories'            => $categories,
            'paymentGateways'       => $paymentSetting?->enabledGateways() ?? [],
            'paymentGateways'       => $paymentSetting?->enabledGateways() ?? [],
            'defaultPaymentGateway' => $defaultGateway,
            'warehouses'            => $warehouses,
            'selected_warehouse_id' => $warehouseId ? (int)$warehouseId : null,
            'taxEnabled'            => TaxHelper::isEnabled(),
            'taxSettings'           => TaxHelper::isEnabled() ? [
                'tax' => TaxHelper::getDefaultTax(),
                'showOnReceipt' => TaxHelper::showOnReceipt(),
            ] : null,
        ]);
    }

    /**
     * searchProduct
     *
     * @param  mixed $request
     * @return void
     */
    public function searchProduct(Request $request)
    {
        // First check product_units for multi-unit barcode lookup
        $productUnit = \App\Models\ProductUnit::where('barcode', $request->barcode)
            ->with(['product', 'unit'])
            ->first();

        if ($productUnit) {
            $product = $productUnit->product;
            return response()->json([
                'success' => true,
                'data'    => [
                    'id' => $product->id,
                    'barcode' => $request->barcode,
                    'title' => $product->title,
                    'description' => $product->description,
                    'image' => $product->image,
                    'sell_price' => $productUnit->sell_price, // Use unit's sell price
                    'buy_price' => $productUnit->buy_price,
                    'stock' => $product->stock,
                    'type' => 'product',
                    // Multi-unit info
                    'unit_id' => $productUnit->unit_id,
                    'unit_name' => $productUnit->unit->name,
                    'conversion_rate' => (float) $productUnit->conversion_rate,
                    'is_unit' => true,
                ],
            ]);
        }

        // Check main product barcode
        $product = Product::where('barcode', $request->barcode)->first();

        if ($product) {
            // Get base unit if exists
            $baseUnit = $product->productUnits()->where('is_base', true)->first();
            
            return response()->json([
                'success' => true,
                'data'    => [
                    'id' => $product->id,
                    'barcode' => $product->barcode,
                    'title' => $product->title,
                    'description' => $product->description,
                    'image' => $product->image,
                    'sell_price' => $baseUnit ? $baseUnit->sell_price : $product->sell_price,
                    'buy_price' => $baseUnit ? $baseUnit->buy_price : $product->buy_price,
                    'stock' => $product->stock,
                    'type' => 'product',
                    // Multi-unit info (base unit or null)
                    'unit_id' => $baseUnit?->unit_id,
                    'unit_name' => $baseUnit?->unit?->name,
                    'conversion_rate' => 1,
                    'is_unit' => false,
                ],
            ]);
        }

        // Find bundle by code
        $bundle = ProductBundle::where('code', $request->barcode)->active()->first();

        if ($bundle) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id' => $bundle->id,
                    'barcode' => $bundle->code,
                    'title' => '[BUNDLE] ' . $bundle->name,
                    'sell_price' => $bundle->sell_price,
                    'stock' => $bundle->available_stock,
                    'type' => 'bundle',
                    'conversion_rate' => 1,
                    'is_unit' => false,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'data'    => null,
        ]);
    }

    /**
     * addToCart
     *
     * @param  mixed $request
     * @return void
     */
    public function addToCart(Request $request)
    {
        $type = $request->type ?? 'product';
        
        if ($type === 'bundle') {
            $bundle = ProductBundle::findOrFail($request->product_id); // using product_id param for bundle id
            
            if ($bundle->available_stock < $request->qty) {
                return redirect()->back()->with('error', "Out of Stock for Bundle components!");
            }

            $cart = Cart::where('bundle_id', $bundle->id)
                ->where('cashier_id', auth()->user()->id)
                ->first();

            if ($cart) {
                $cart->increment('qty', $request->qty);
                $cart->price = $bundle->sell_price * $cart->qty;
                $cart->save();
            } else {
                Cart::create([
                    'cashier_id' => auth()->user()->id,
                    'bundle_id'  => $bundle->id,
                    'qty'        => $request->qty,
                    'price'      => $bundle->sell_price * $request->qty,
                ]);
            }
        } else {
            // Original product logic
            $product = Product::whereId($request->product_id)->first();

            if (! $product) {
                return redirect()->back()->with('error', 'Product not found.');
            }

            $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
            ]);
            
            $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();
                
            $currentStock = $productWarehouse ? $productWarehouse->stock : 0;

            if ($currentStock < $request->qty) {
                return redirect()->back()->with('error', "Out of Stock! (Available: {$currentStock})");
            }

            $cart = Cart::where('product_id', $request->product_id)
                ->where('cashier_id', auth()->user()->id)
                ->first();

            if ($cart) {
                $cart->increment('qty', $request->qty);
                $cart->price = $product->sell_price * $cart->qty;
                $cart->save();
            } else {
                Cart::create([
                    'cashier_id' => auth()->user()->id,
                    'product_id' => $request->product_id,
                    'qty'        => $request->qty,
                    'price'      => $request->sell_price * $request->qty,
                ]);
            }
        }

        return redirect()->route('transactions.index', ['warehouse_id' => $request->warehouse_id])->with('success', 'Item Added Successfully!.');
    }

    /**
     * destroyCart
     *
     * @param  mixed $request
     * @return void
     */
    public function destroyCart($cart_id)
    {
        $cart = Cart::with('product')->whereId($cart_id)->first();

        if ($cart) {
            $cart->delete();
            return back();
        } else {
            // Handle case where no cart is found (e.g., redirect with error message)
            return back()->withErrors(['message' => 'Cart not found']);
        }

    }

    /**
     * updateCart - Update cart item quantity
     *
     * @param  mixed $request
     * @param  int $cart_id
     * @return void
     */
    public function updateCart(Request $request, $cart_id)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        $cart = Cart::with('product')->whereId($cart_id)
            ->where('cashier_id', auth()->user()->id)
            ->first();

        if (! $cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        // Check stock availability
        // We need warehouse_id here. If not passed, we can't check efficiently.
        // For now, let's skip strict check here or require frontend to pass it?
        // Assuming frontend passes warehouse_id when updating cart? 
        // Or just rely on checkout validation. 
        // Let's rely on checkout validation to avoid breaking existing updateCart API structure too much if not changed in frontend yet.
        // But better: use warehouse_id if exists.
        
        if ($request->warehouse_id) {
             $productWarehouse = ProductWarehouse::where('product_id', $cart->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();
             $currentStock = $productWarehouse ? $productWarehouse->stock : 0;
             
             if ($currentStock < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Tersedia: ' . $currentStock,
                ], 422);
            }
        }

        // Update quantity and price
        $cart->qty   = $request->qty;
        $cart->price = $cart->product->sell_price * $request->qty;
        $cart->save();

        return back()->with('success', 'Quantity updated successfully');
    }

    /**
     * holdCart - Hold current cart items for later
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function holdCart(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:50',
        ]);

        $userId = auth()->user()->id;

        // Get active cart items
        $activeCarts = Cart::where('cashier_id', $userId)
            ->active()
            ->get();

        if ($activeCarts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong, tidak ada yang bisa ditahan',
            ], 422);
        }

        // Generate unique hold ID
        $holdId = 'HOLD-' . strtoupper(uniqid());
        $label  = $request->label ?: 'Transaksi ' . now()->format('H:i');

        // Mark all active cart items as held
        Cart::where('cashier_id', $userId)
            ->active()
            ->update([
                'hold_id'    => $holdId,
                'hold_label' => $label,
                'held_at'    => now(),
            ]);

        return back()->with('success', 'Transaksi ditahan: ' . $label);
    }

    /**
     * resumeCart - Resume a held cart
     *
     * @param  string $holdId
     * @return \Illuminate\Http\JsonResponse
     */
    public function resumeCart($holdId)
    {
        $userId = auth()->user()->id;

        // Check if there are any active carts (not held)
        $activeCarts = Cart::where('cashier_id', $userId)
            ->active()
            ->count();

        if ($activeCarts > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Selesaikan atau tahan transaksi aktif terlebih dahulu',
            ], 422);
        }

        // Get held carts
        $heldCarts = Cart::where('cashier_id', $userId)
            ->forHold($holdId)
            ->get();

        if ($heldCarts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ditahan tidak ditemukan',
            ], 404);
        }

        // Resume by clearing hold info
        Cart::where('cashier_id', $userId)
            ->forHold($holdId)
            ->update([
                'hold_id'    => null,
                'hold_label' => null,
                'held_at'    => null,
            ]);

        return back()->with('success', 'Transaksi dilanjutkan');
    }

    /**
     * clearHold - Delete a held cart
     *
     * @param  string $holdId
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearHold($holdId)
    {
        $userId = auth()->user()->id;

        $deleted = Cart::where('cashier_id', $userId)
            ->forHold($holdId)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ditahan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaksi ditahan berhasil dihapus',
        ]);
    }

    /**
     * getHeldCarts - Get all held carts for current user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHeldCarts()
    {
        $userId = auth()->user()->id;

        $heldCarts = Cart::with('product:id,title,sell_price,image')
            ->where('cashier_id', $userId)
            ->held()
            ->get()
            ->groupBy('hold_id')
            ->map(function ($items, $holdId) {
                $first = $items->first();
                return [
                    'hold_id'     => $holdId,
                    'label'       => $first->hold_label,
                    'held_at'     => $first->held_at,
                    'items_count' => $items->sum('qty'),
                    'total'       => $items->sum('price'),
                    'items'       => $items->map(fn($item) => [
                        'id'      => $item->id,
                        'product' => $item->product,
                        'qty'     => $item->qty,
                        'price'   => $item->price,
                    ]),
                ];
            })
            ->values();

        return response()->json([
            'success'    => true,
            'held_carts' => $heldCarts,
        ]);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request, PaymentGatewayManager $paymentGatewayManager)
    {
        // Validate request
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'discount' => 'nullable|integer|min:0',
            'discount_type' => 'nullable|in:amount,percent',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'event_discount' => 'nullable|integer|min:0',
            'event_discount_type' => 'nullable|in:amount,percent',
            'event_discount_percent' => 'nullable|numeric|min:0|max:100',
            'grand_total' => 'required|integer|min:0',
            'cash' => 'required|integer|min:0',
            'change' => 'nullable|integer|min:0',
            'payment_gateway' => 'nullable|string',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $paymentGateway = $request->input('payment_gateway');
        if ($paymentGateway) {
            $paymentGateway = strtolower($paymentGateway);
        }
        $paymentSetting = null;

        if ($paymentGateway) {
            $paymentSetting = PaymentSetting::first();

            if (! $paymentSetting || ! $paymentSetting->isGatewayReady($paymentGateway)) {
                return redirect()
                    ->route('transactions.index')
                    ->with('error', 'Gateway pembayaran belum dikonfigurasi.');
            }
        }

        $length = 10;
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
        }

        $invoice       = 'TRX-' . Str::upper($random);
        $isCashPayment = empty($paymentGateway);
        $cashAmount    = $isCashPayment ? $request->cash : $request->grand_total;
        $changeAmount  = $isCashPayment ? $request->change : 0;

        $transaction = DB::transaction(function () use (
            $request,
            $invoice,
            $cashAmount,
            $changeAmount,
            $paymentGateway,
            $isCashPayment
        ) {
            // Calculate tax if enabled
            $subtotal = $request->grand_total; // This is the pre-tax subtotal from frontend
            $taxId = null;
            $taxAmount = 0;
            $taxRate = 0;
            $grandTotal = $request->grand_total;

            if (TaxHelper::isEnabled()) {
                $tax = TaxHelper::getDefaultTax();
                if ($tax) {
                    $taxId = $tax->id;
                    $taxRate = $tax->rate;
                    // Calculate tax based on subtotal (after discounts from frontend)
                    $taxAmount = $tax->calculateTax($subtotal);
                    $grandTotal = $tax->getTotal($subtotal);
                }
            }

            $transaction = Transaction::create([
                'cashier_id'     => auth()->user()->id,
                'customer_id'    => $request->customer_id,
                'warehouse_id'   => $request->warehouse_id,
                'invoice'        => $invoice,
                'cash'           => $cashAmount,
                'change'         => $changeAmount,
                'discount'       => $request->discount ?? 0,
                'discount_type' => $request->discount_type ?? 'amount',
                'discount_percent' => $request->discount_type === 'percent' ? $request->discount_percent : null,
                'event_discount' => $request->event_discount ?? 0,
                'event_discount_type' => $request->event_discount_type ?? 'amount',
                'event_discount_percent' => $request->event_discount_type === 'percent' ? $request->event_discount_percent : null,
                'subtotal'       => $subtotal,
                'tax_id'         => $taxId,
                'tax_amount'     => $taxAmount,
                'tax_rate'       => $taxRate,
                'grand_total'    => $grandTotal,
                'payment_method' => $paymentGateway ?: 'cash',
                'payment_status' => $isCashPayment ? 'paid' : 'pending',
            ]);

            $carts = Cart::where('cashier_id', auth()->user()->id)->get();

            foreach ($carts as $cart) {
                // Get weight from product or bundle
                $weight = 0;
                if ($cart->bundle_id) {
                    $bundleForWeight = ProductBundle::find($cart->bundle_id);
                    $weight = $bundleForWeight?->weight ?? 0;
                } elseif ($cart->product_id) {
                    $productForWeight = Product::find($cart->product_id);
                    $weight = $productForWeight?->weight ?? 0;
                }

                $transaction->details()->create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $cart->product_id,
                    'bundle_id'      => $cart->bundle_id,
                    'qty'            => $cart->qty,
                    'price'          => $cart->price,
                    'weight'         => $weight,
                ]);

                if ($cart->bundle_id) {
                    // Handle Bundle Stock Deduction
                    $bundle = ProductBundle::with('items.product')->find($cart->bundle_id);
                    $total_bundle_buy_price = 0;
                    
                    foreach ($bundle->items as $item) {
                        $qty_to_deduct = $item->qty * $cart->qty;
                        $total_bundle_buy_price += ($item->product->buy_price * $qty_to_deduct);

                        $pw = ProductWarehouse::where('product_id', $item->product_id)
                            ->where('warehouse_id', $request->warehouse_id)
                            ->first();
                        
                        $prev = $pw ? $pw->stock : 0;
                        if ($prev < $qty_to_deduct) {
                            throw new \Exception("Stok komponen {$item->product->title} tidak mencukupi.");
                        }

                        $pw->stock = $prev - $qty_to_deduct;
                        $pw->save();

                        ProductStock::create([
                            'product_id'     => $item->product_id,
                            'warehouse_id'   => $request->warehouse_id,
                            'type'           => 'out',
                            'qty'            => $qty_to_deduct,
                            'previous_stock' => $prev,
                            'current_stock'  => $pw->stock,
                            'transaction_id' => $transaction->id,
                            'user_id'        => auth()->id(),
                            'note'           => 'Bundle Sale: ' . $bundle->name . ' (Invoice: ' . $invoice . ')',
                        ]);
                    }

                    $profits = $cart->price - $total_bundle_buy_price;

                } else {
                    // Original Product Stock Deduction
                    $total_buy_price  = $cart->product->buy_price * $cart->qty;
                    $total_sell_price = $cart->product->sell_price * $cart->qty;
                    $profits          = $total_sell_price - $total_buy_price;

                    $productWarehouse = ProductWarehouse::where('product_id', $cart->product_id)
                        ->where('warehouse_id', $request->warehouse_id)
                        ->first();
                        
                    $currentStock = $productWarehouse ? $productWarehouse->stock : 0;

                    if ($currentStock < $cart->qty) {
                        throw new \Exception("Stok tidak mencukupi untuk produk: {$cart->product->title}. Tersedia: {$currentStock}");
                    }

                    $previous_stock = $currentStock;
                    $productWarehouse->stock = $currentStock - $cart->qty;
                    $productWarehouse->save();

                    ProductStock::create([
                        'product_id'     => $cart->product_id,
                        'warehouse_id'   => $request->warehouse_id,
                        'type'           => 'out',
                        'qty'            => $cart->qty,
                        'previous_stock' => $previous_stock,
                        'current_stock'  => $productWarehouse->stock,
                        'transaction_id' => $transaction->id,
                        'user_id'        => auth()->id(),
                        'note'           => 'Transaction ' . $invoice,
                    ]);
                }

                $transaction->profits()->create([
                    'transaction_id' => $transaction->id,
                    'total'          => $profits,
                ]);
            }

            Cart::where('cashier_id', auth()->user()->id)->delete();

            return $transaction->fresh(['customer']);
        });

        if ($paymentGateway) {
            try {
                $paymentResponse = $paymentGatewayManager->createPayment($transaction, $paymentGateway, $paymentSetting);

                $transaction->update([
                    'payment_reference' => $paymentResponse['reference'] ?? null,
                    'payment_url'       => $paymentResponse['payment_url'] ?? null,
                ]);
            } catch (PaymentGatewayException $exception) {
                return redirect()
                    ->route('transactions.print', $transaction->invoice)
                    ->with('error', $exception->getMessage());
            }
        }

        return to_route('transactions.print', $transaction->invoice);
    }

    public function print($invoice)
    {
        //get transaction
        $transaction = Transaction::with(['details.product', 'details.bundle.items.product', 'cashier', 'customer'])->where('invoice', $invoice)->firstOrFail();

        return Inertia::render('Dashboard/Transactions/Print', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Display transaction history.
     */
    public function history(Request $request)
    {
        $filters = [
            'invoice'    => $request->input('invoice'),
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'type'       => $request->input('type'),
        ];

        $query = Transaction::query()
            ->with(['cashier:id,name', 'customer:id,name'])
            ->withSum('details as total_items', 'qty')
            ->withSum('profits as total_profit', 'total')
            ->orderByDesc('created_at');

        if (! $request->user()->isSuperAdmin()) {
            $query->where('cashier_id', $request->user()->id);
        }

        $query
            ->when($filters['invoice'], function (Builder $builder, $invoice) {
                $builder->where('invoice', 'like', '%' . $invoice . '%');
            })
            ->when($filters['start_date'], function (Builder $builder, $date) {
                $builder->whereDate('created_at', '>=', $date);
            })
            ->when($filters['end_date'], function (Builder $builder, $date) {
                $builder->whereDate('created_at', '<=', $date);
            })
            ->when($filters['type'], function (Builder $builder, $type) {
                if ($type === 'bundle') {
                    $builder->whereHas('details', fn($q) => $q->whereNotNull('bundle_id'));
                } elseif ($type === 'product') {
                    $builder->whereHas('details', fn($q) => $q->whereNotNull('product_id'));
                }
            });

        $transactions = $query->paginate(10)->withQueryString();

        return Inertia::render('Dashboard/Transactions/History', [
            'transactions' => $transactions,
            'filters'      => $filters,
        ]);
    }
}
