<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ImportLog;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use App\Models\Sale;
use App\Models\SaleApproval;
use App\Models\SaleImport;
use App\Models\SaleDetailImport;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use App\Services\JournalService;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sales = Sale::with(['customer', 'warehouse'])
            ->when($request->q, function ($query, $q) {
                $query->where('invoice', 'like', '%' . $q . '%');
            })
            ->when($request->start_date, function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($request->end_date, function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->when($request->approval_status, function ($query, $status) {
                $query->where('approval_status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $importLogs = ImportLog::with('user')
            ->where('type', 'sale')
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/Sales/Index', [
            'sales' => $sales,
            'importLogs' => $importLogs,
            'filters' => $request->all(['q', 'start_date', 'end_date', 'approval_status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::with('addresses')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/Sales/Create', [
            'customers' => $customers,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'grand_total' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0',
            'discount_type' => 'nullable|in:amount,percent',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'event_discount' => 'nullable|integer|min:0',
            'event_discount_type' => 'nullable|in:amount,percent',
            'event_discount_percent' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'required|integer|min:0',
            'other_cost' => 'required|integer|min:0',
            'payment_type' => 'required|in:cash,tempo',
            'shipping_type' => 'required|in:cod,pickup,courier',
            'shipping_name' => 'nullable|string|max:255',
            'shipping_phone' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'sender_name' => 'nullable|string|max:255',
            'sender_phone' => 'nullable|string|max:255',
            'is_preorder' => 'required|boolean',
            'estimated_ready_date' => 'required_if:is_preorder,true|nullable|date',
            'paid_amount' => 'required_if:is_preorder,true|nullable|integer|min:0',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.bundle_id'  => 'nullable|exists:product_bundles,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.sell_price' => 'required|integer|min:0',
            'items.*.discount'   => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate Invoice
            $length = 10;
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
            }
            $invoice = 'SL-' . Str::upper($random);

            $sale = Sale::create([
                'invoice'      => $invoice,
                'customer_id'  => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'user_id'      => auth()->id(),
                'grand_total'  => $request->grand_total,
                'discount'     => $request->discount ?? 0,
                'discount_type' => $request->discount_type ?? 'amount',
                'discount_percent' => $request->discount_type === 'percent' ? $request->discount_percent : null,
                'event_discount' => $request->event_discount ?? 0,
                'event_discount_type' => $request->event_discount_type ?? 'amount',
                'event_discount_percent' => $request->event_discount_type === 'percent' ? $request->event_discount_percent : null,
                'shipping_cost' => $request->shipping_cost,
                'other_cost'   => $request->other_cost,
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'sender_name' => $request->sender_name,
                'sender_phone' => $request->sender_phone,
                'payment_type' => $request->payment_type,
                'shipping_type' => $request->shipping_type,
                'is_preorder' => $request->is_preorder,
                'preorder_status' => $request->is_preorder ? 'pending' : null,
                'estimated_ready_date' => $request->is_preorder ? $request->estimated_ready_date : null,
                'paid_amount' => $request->is_preorder ? ($request->paid_amount ?? 0) : 0,
                'notes'        => $request->notes,
                'status'       => 'draft',
            ]);

            foreach ($request->items as $item) {
                // Get weight from product or bundle
                $weight = 0;
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    $weight = $product?->weight ?? 0;
                } elseif (!empty($item['bundle_id'])) {
                    $bundle = ProductBundle::find($item['bundle_id']);
                    $weight = $bundle?->weight ?? 0;
                }

                $sale->details()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'bundle_id'  => $item['bundle_id'] ?? null,
                    'qty'        => $item['qty'],
                    'sell_price' => $item['sell_price'],
                    'discount'   => $item['discount'] ?? 0,
                    'weight'     => $weight,
                ]);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sale created as draft. Please finalize to update stock.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sale = Sale::with(['customer', 'warehouse', 'details.product', 'details.bundle.items.product', 'user'])->findOrFail($id);

        return Inertia::render('Dashboard/Sales/Show', [
            'sale' => $sale,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $sale = Sale::with(['details.product' => function($query) use ($id) {
            $s = Sale::find($id);
            if ($s) {
                $query->leftJoin('product_warehouses', function($join) use ($s) {
                    $join->on('products.id', '=', 'product_warehouses.product_id')
                        ->where('product_warehouses.warehouse_id', '=', $s->warehouse_id);
                })->select('products.*', DB::raw('COALESCE(product_warehouses.stock, 0) as stock'));
            }
        }, 'details.bundle'])->findOrFail($id);

        if ($sale->status === 'finalized') {
            return redirect()->route('sales.index')->with('error', 'Cannot edit finalized sale.');
        }

        $customers = Customer::with('addresses')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return Inertia::render('Dashboard/Sales/Edit', [
            'sale' => $sale,
            'customers' => $customers,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        if ($sale->status === 'finalized') {
            return redirect()->route('sales.index')->with('error', 'Cannot edit finalized sale.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'grand_total' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0',
            'discount_type' => 'nullable|in:amount,percent',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'event_discount' => 'nullable|integer|min:0',
            'event_discount_type' => 'nullable|in:amount,percent',
            'event_discount_percent' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'required|integer|min:0',
            'other_cost' => 'required|integer|min:0',
            'payment_type' => 'required|in:cash,tempo',
            'shipping_type' => 'required|in:cod,pickup,courier',
            'shipping_name' => 'nullable|string|max:255',
            'shipping_phone' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'sender_name' => 'nullable|string|max:255',
            'sender_phone' => 'nullable|string|max:255',
            'is_preorder' => 'required|boolean',
            'estimated_ready_date' => 'required_if:is_preorder,true|nullable|date',
            'paid_amount' => 'required_if:is_preorder,true|nullable|integer|min:0',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.bundle_id'  => 'nullable|exists:product_bundles,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.sell_price' => 'required|integer|min:0',
            'items.*.discount'   => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $sale) {
            $sale->update([
                'customer_id'  => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'grand_total'  => $request->grand_total,
                'discount'     => $request->discount ?? 0,
                'discount_type' => $request->discount_type ?? 'amount',
                'discount_percent' => $request->discount_type === 'percent' ? $request->discount_percent : null,
                'event_discount' => $request->event_discount ?? 0,
                'event_discount_type' => $request->event_discount_type ?? 'amount',
                'event_discount_percent' => $request->event_discount_type === 'percent' ? $request->event_discount_percent : null,
                'shipping_cost' => $request->shipping_cost,
                'other_cost'   => $request->other_cost,
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'sender_name' => $request->sender_name,
                'sender_phone' => $request->sender_phone,
                'payment_type' => $request->payment_type,
                'shipping_type' => $request->shipping_type,
                'is_preorder' => $request->is_preorder,
                'preorder_status' => $request->is_preorder ? ($sale->preorder_status ?? 'pending') : null,
                'estimated_ready_date' => $request->is_preorder ? $request->estimated_ready_date : null,
                'paid_amount' => $request->is_preorder ? ($request->paid_amount ?? 0) : 0,
                'notes'        => $request->notes,
            ]);

            $sale->details()->delete();

            foreach ($request->items as $item) {
                // Get weight from product or bundle
                $weight = 0;
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    $weight = $product?->weight ?? 0;
                } elseif (!empty($item['bundle_id'])) {
                    $bundle = ProductBundle::find($item['bundle_id']);
                    $weight = $bundle?->weight ?? 0;
                }

                $sale->details()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'bundle_id'  => $item['bundle_id'] ?? null,
                    'qty'        => $item['qty'],
                    'sell_price' => $item['sell_price'],
                    'discount'   => $item['discount'] ?? 0,
                    'weight'     => $weight,
                ]);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);

        if ($sale->status === 'finalized') {
            return redirect()->route('sales.index')->with('error', 'Cannot delete finalized sale.');
        }

        $sale->details()->delete();
        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
    }

    /**
     * Finalize sale and update stock.
     */
    public function finalize($id)
    {
        $sale = Sale::with('details.product')->findOrFail($id);

        if ($sale->status === 'finalized') {
            return redirect()->route('sales.index')->with('error', 'Sale already finalized.');
        }

        DB::transaction(function () use ($sale) {
            $totalProfit = 0;

            foreach ($sale->details as $detail) {
                if ($detail->product_id) {
                    $product = $detail->product;
                    $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                        ->where('warehouse_id', $sale->warehouse_id)
                        ->first();

                    if (!$productWarehouse) {
                        // Create record if not exists (especially for PO where stock might be 0)
                        $productWarehouse = ProductWarehouse::create([
                            'product_id' => $product->id,
                            'warehouse_id' => $sale->warehouse_id,
                            'stock' => 0,
                            'booked_stock' => 0,
                        ]);
                    }

                    if ($sale->is_preorder) {
                        // For PO: just book the stock, don't reduce actual stock yet
                        $productWarehouse->booked_stock += $detail->qty;
                        $productWarehouse->save();
                        
                        // Profit is not calculated yet for PO (calculated at final completion)
                    } else {
                        // Normal Sale: Check and reduce stock
                        if ($productWarehouse->stock < $detail->qty) {
                            throw new \Exception("Stok tidak mencukupi untuk: " . $product->title);
                        }

                        $previous_stock = $productWarehouse->stock;
                        $productWarehouse->stock -= $detail->qty;
                        $productWarehouse->save();

                        // Calculate profit for this item
                        $totalProfit += ($detail->sell_price - $product->buy_price) * $detail->qty;

                        // Log Stock Mutation
                        ProductStock::create([
                            'product_id'     => $product->id,
                            'warehouse_id'   => $sale->warehouse_id,
                            'type'           => 'out',
                            'qty'            => $detail->qty,
                            'previous_stock' => $previous_stock,
                            'current_stock'  => $productWarehouse->stock,
                            'user_id'        => auth()->id(),
                            'sale_id'        => $sale->id,
                            'note'           => 'Sale ' . $sale->invoice . ' finalized',
                        ]);
                    }
                } else if ($detail->bundle_id) {
                    $bundle = ProductBundle::with('items.product')->find($detail->bundle_id);
                    $buyPriceTotal = 0;

                    foreach ($bundle->items as $bundleItem) {
                        $product = $bundleItem->product;
                        $neededQty = $bundleItem->qty * $detail->qty;

                        $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                            ->where('warehouse_id', $sale->warehouse_id)
                            ->first();

                        if (!$productWarehouse) {
                            $productWarehouse = ProductWarehouse::create([
                                'product_id' => $product->id,
                                'warehouse_id' => $sale->warehouse_id,
                                'stock' => 0,
                                'booked_stock' => 0,
                            ]);
                        }

                        if ($sale->is_preorder) {
                            // For PO Bundle: book components
                            $productWarehouse->booked_stock += $neededQty;
                            $productWarehouse->save();
                        } else {
                            // Normal Bundle: Check and reduce components
                            if ($productWarehouse->stock < $neededQty) {
                                throw new \Exception("Stok tidak mencukupi untuk komponen: " . $product->title);
                            }

                            $previous_stock = $productWarehouse->stock;
                            $productWarehouse->stock -= $neededQty;
                            $productWarehouse->save();

                            $buyPriceTotal += $product->buy_price * $bundleItem->qty;

                            // Log Stock Mutation for component
                            ProductStock::create([
                                'product_id'     => $product->id,
                                'warehouse_id'   => $sale->warehouse_id,
                                'type'           => 'out',
                                'qty'            => $neededQty,
                                'previous_stock' => $previous_stock,
                                'current_stock'  => $productWarehouse->stock,
                                'user_id'        => auth()->id(),
                                'sale_id'        => $sale->id,
                                'note'           => 'Sale ' . $sale->invoice . ' finalized (Bundle: ' . $bundle->name . ')',
                            ]);
                        }
                    }

                    if (!$sale->is_preorder) {
                        // Profit based on total bundle sell price - total component buy price
                        $totalProfit += ($detail->sell_price - $buyPriceTotal) * $detail->qty;
                    }
                }
            }

            if (!$sale->is_preorder) {
                // Add shipping and other costs to total profit
                $totalProfit += $sale->shipping_cost + $sale->other_cost;

                // Create Profit record
                $sale->profits()->create([
                    'total' => $totalProfit,
                ]);
            }

            // NOTE: Journal entries are created when Finance approves the sale
            // See SaleApprovalController::financeApprove()

            // Create initial approval record for finance
            SaleApproval::create([
                'sale_id' => $sale->id,
                'type' => 'finance',
                'status' => 'pending',
                'user_id' => null,
                'notes' => null,
                'approved_at' => null,
            ]);

            $updateData = [
                'status'          => 'finalized',
                'approval_status' => 'pending_finance',
                'finalized_at'    => now(),
            ];

            if ($sale->is_preorder) {
                // Determine if stock is already ready (very rare for PO but possible)
                // For now, keep it as 'pending' until Purchase fulfills it
                $updateData['preorder_status'] = 'pending';
            }

            $sale->update($updateData);
        });

        $msg = $sale->is_preorder ? 'Pre-Order difinalize dan menunggu approval Finance.' : 'Transaksi difinalize dan menunggu approval Finance.';
        return redirect()->route('sales.index')->with('success', $msg);
    }

    /**
     * Search product for sale input
     */
    public function searchProduct(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $q = $request->q;

        $products = Product::where(function($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('barcode', 'like', '%' . $q . '%');
            })
            ->leftJoin('product_warehouses', function($join) use ($warehouseId) {
                $join->on('products.id', '=', 'product_warehouses.product_id')
                    ->where('product_warehouses.warehouse_id', '=', $warehouseId);
            })
            ->select('products.*', DB::raw('COALESCE(product_warehouses.stock, 0) as stock'))
            ->limit(10)
            ->get()
            ->map(function($p) {
                $p->type = 'product';
                return $p;
            });

        $bundles = ProductBundle::where('is_active', true)
            ->where(function($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('code', 'like', '%' . $q . '%');
            })
            ->limit(10)
            ->get()
            ->map(function($b) use ($warehouseId) {
                return (object)[
                    'id' => $b->id,
                    'title' => '[BUNDLE] ' . $b->name,
                    'barcode' => $b->code,
                    'sell_price' => $b->sell_price,
                    'stock' => $b->getStockForWarehouse($warehouseId),
                    'type' => 'bundle',
                    'image' => $b->image,
                ];
            });

        return response()->json($products->concat($bundles));
    }

    /**
     * Download Excel template for import
     */
    public function downloadTemplate()
    {
        $header = [
            ['Template Import Transaksi Penjualan'],
            [''],
            ['Petunjuk:'],
            ['1. Isi data sesuai kolom yang tersedia'],
            ['2. customer_no_telp: Nomor telepon customer yang sudah terdaftar'],
            ['3. warehouse_name: Nama gudang yang sudah terdaftar'],
            ['4. product_barcode: Barcode produk yang sudah terdaftar'],
            ['5. qty: Jumlah produk (angka)'],
            ['6. sell_price: Harga jual per item (opsional, kosongkan untuk harga default)'],
            ['7. discount: Diskon per item (opsional, default 0)'],
            ['8. payment_type: cash atau tempo'],
            ['9. shipping_name: Nama penerima (opsional)'],
            ['10. shipping_phone: No. Telp penerima (opsional)'],
            ['11. shipping_address: Alamat pengiriman (opsional)'],
            ['12. notes: Catatan transaksi (opsional)'],
            [''],
            ['customer_no_telp', 'warehouse_name', 'product_barcode', 'qty', 'sell_price', 'discount', 'payment_type', 'shipping_name', 'shipping_phone', 'shipping_address', 'notes'],
            ['08123456789', 'Gudang Utama', 'PRD001', 2, '', '', 'cash', 'John Doe', '08111222333', 'Jl. Contoh No. 123', 'Contoh transaksi'],
            ['08123456789', 'Gudang Utama', 'PRD002', 1, 50000, 5000, 'tempo', '', '', '', ''],
        ];

        $xlsx = SimpleXLSXGen::fromArray($header);
        
        return response()->streamDownload(function() use ($xlsx) {
            echo $xlsx;
        }, 'template_import_transaksi.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import sales from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();

        // Create import log
        $importLog = ImportLog::create([
            'user_id' => auth()->id(),
            'type' => 'sale',
            'filename' => $filename,
            'status' => 'processing',
        ]);

        try {
            $xlsx = SimpleXLSX::parse($file->getPathname());
            
            if (!$xlsx) {
                throw new \Exception('Gagal membaca file Excel: ' . SimpleXLSX::parseError());
            }

            $rows = $xlsx->rows();
            $errors = [];
            $successCount = 0;
            $dataStartRow = 0;

            // Find the header row (contains 'customer_no_telp')
            foreach ($rows as $index => $row) {
                if (isset($row[0]) && strtolower(trim($row[0])) === 'customer_no_telp') {
                    $dataStartRow = $index + 1;
                    break;
                }
            }

            $dataRows = array_slice($rows, $dataStartRow);
            $totalRows = count($dataRows);

            // Group rows by customer_phone for creating single transaction per customer
            $groupedData = [];
            foreach ($dataRows as $index => $row) {
                $rowNumber = $dataStartRow + $index + 1;
                
                if (empty($row[0]) || empty($row[2])) continue; // Skip empty rows

                $customerPhone = trim($row[0]);
                $warehouseName = trim($row[1] ?? '');
                $productBarcode = trim($row[2]);
                $qty = intval($row[3] ?? 1);
                $sellPrice = !empty($row[4]) ? intval($row[4]) : null;
                $discount = intval($row[5] ?? 0);
                $paymentType = strtolower(trim($row[6] ?? 'cash'));
                $shippingName = trim($row[7] ?? '');
                $shippingPhone = trim($row[8] ?? '');
                $shippingAddress = trim($row[9] ?? '');
                $notes = trim($row[10] ?? '');

                // Validate customer
                $customer = Customer::where('no_telp', $customerPhone)->first();
                if (!$customer) {
                    $errors[] = "Baris {$rowNumber}: Customer dengan no_telp '{$customerPhone}' tidak ditemukan";
                    continue;
                }

                // Validate warehouse
                $warehouse = Warehouse::where('name', 'like', '%' . $warehouseName . '%')->first();
                if (!$warehouse) {
                    $errors[] = "Baris {$rowNumber}: Gudang '{$warehouseName}' tidak ditemukan";
                    continue;
                }

                // Validate product
                $product = Product::where('barcode', $productBarcode)->first();
                if (!$product) {
                    $errors[] = "Baris {$rowNumber}: Produk dengan barcode '{$productBarcode}' tidak ditemukan";
                    continue;
                }

                // Check stock
                $warehouseStock = ProductWarehouse::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();
                
                if (!$warehouseStock || $warehouseStock->stock < $qty) {
                    $availableStock = $warehouseStock ? $warehouseStock->stock : 0;
                    $errors[] = "Baris {$rowNumber}: Stok produk '{$product->title}' tidak mencukupi (tersedia: {$availableStock}, diminta: {$qty})";
                    continue;
                }

                // Group by customer+warehouse key
                $groupKey = $customer->id . '_' . $warehouse->id . '_' . $paymentType;
                if (!isset($groupedData[$groupKey])) {
                    $groupedData[$groupKey] = [
                        'customer' => $customer,
                        'warehouse' => $warehouse,
                        'payment_type' => $paymentType,
                        'shipping_name' => $shippingName,
                        'shipping_phone' => $shippingPhone,
                        'shipping_address' => $shippingAddress,
                        'notes' => $notes,
                        'items' => [],
                    ];
                }

                $groupedData[$groupKey]['items'][] = [
                    'product' => $product,
                    'qty' => $qty,
                    'sell_price' => $sellPrice ?? $product->sell_price,
                    'discount' => $discount,
                ];
            }

            // Create sales from grouped data
            DB::transaction(function () use ($groupedData, &$successCount, $importLog) {
                foreach ($groupedData as $data) {
                    // Generate Invoice
                    $random = '';
                    for ($i = 0; $i < 10; $i++) {
                        $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
                    }
                    $invoice = 'IMP-' . Str::upper($random);

                    $grandTotal = 0;
                    foreach ($data['items'] as $item) {
                        $grandTotal += ($item['sell_price'] * $item['qty']) - $item['discount'];
                    }

                    $saleImport = SaleImport::create([
                        'import_log_id' => $importLog->id,
                        'invoice' => $invoice,
                        'customer_id' => $data['customer']->id,
                        'warehouse_id' => $data['warehouse']->id,
                        'user_id' => auth()->id(),
                        'grand_total' => $grandTotal,
                        'discount' => 0,
                        'payment_type' => $data['payment_type'],
                        'shipping_type' => !empty($data['shipping_address']) ? 'delivery' : 'pickup',
                        'shipping_name' => $data['shipping_name'],
                        'shipping_phone' => $data['shipping_phone'],
                        'shipping_address' => $data['shipping_address'],
                        'notes' => $data['notes'] . ' [Import Excel]',
                        'status' => 'draft',
                    ]);

                    foreach ($data['items'] as $item) {
                        $saleImport->details()->create([
                            'product_id' => $item['product']->id,
                            'qty' => $item['qty'],
                            'sell_price' => $item['sell_price'],
                            'discount' => $item['discount'],
                            'weight' => $item['product']->weight ?? 0,
                        ]);
                    }

                    $successCount++;
                }
            });

            // Update import log
            $importLog->update([
                'total_rows' => $totalRows,
                'success_count' => $successCount,
                'failed_count' => count($errors),
                'errors' => $errors,
                'status' => count($errors) > 0 && $successCount === 0 ? 'failed' : 'completed',
            ]);

            $message = "Import selesai: {$successCount} transaksi berhasil dibuat";
            if (count($errors) > 0) {
                $message .= ", " . count($errors) . " baris gagal";
            }

            return redirect()->route('sales.index')->with('success', $message);

        } catch (\Exception $e) {
            $importLog->update([
                'status' => 'failed',
                'errors' => [$e->getMessage()],
            ]);

            return redirect()->route('sales.index')->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Get import log errors
     */
    public function importErrors($id)
    {
        $log = ImportLog::findOrFail($id);
        return response()->json([
            'errors' => $log->errors ?? [],
            'filename' => $log->filename,
            'total_rows' => $log->total_rows,
            'success_count' => $log->success_count,
            'failed_count' => $log->failed_count,
        ]);
    }

    /**
     * Show import detail page
     */
    public function importShow($id)
    {
        $importLog = ImportLog::with('user')->findOrFail($id);
        
        $salesImport = SaleImport::with(['customer', 'warehouse', 'user', 'details.product'])
            ->where('import_log_id', $id)
            ->get();

        // Calculate stock comparison - aggregate qty per product per warehouse
        $stockComparison = [];
        foreach ($salesImport as $sale) {
            $warehouseId = $sale->warehouse_id;
            $warehouseName = $sale->warehouse->name ?? 'Unknown';
            
            foreach ($sale->details as $detail) {
                $productId = $detail->product_id;
                $key = "{$productId}_{$warehouseId}";
                
                if (!isset($stockComparison[$key])) {
                    // Get current stock
                    $currentStock = ProductWarehouse::where('product_id', $productId)
                        ->where('warehouse_id', $warehouseId)
                        ->first();
                    
                    $stockComparison[$key] = [
                        'product_id' => $productId,
                        'product_title' => $detail->product->title ?? '-',
                        'product_barcode' => $detail->product->barcode ?? '-',
                        'warehouse_id' => $warehouseId,
                        'warehouse_name' => $warehouseName,
                        'total_qty' => 0,
                        'current_stock' => $currentStock ? $currentStock->stock : 0,
                    ];
                }
                
                $stockComparison[$key]['total_qty'] += $detail->qty;
            }
        }

        // Add status and convert to array
        $stockComparison = collect($stockComparison)->map(function ($item) {
            $item['is_sufficient'] = $item['current_stock'] >= $item['total_qty'];
            $item['shortage'] = max(0, $item['total_qty'] - $item['current_stock']);
            return $item;
        })->values()->toArray();

        return Inertia::render('Dashboard/Sales/ImportShow', [
            'importLog' => $importLog,
            'salesImport' => $salesImport,
            'stockComparison' => $stockComparison,
        ]);
    }

    /**
     * Finalize all imported sales - move to main sales table and update stock
     */
    public function finalizeImport($id)
    {
        $importLog = ImportLog::findOrFail($id);
        
        $salesImport = SaleImport::with(['customer', 'warehouse', 'details.product'])
            ->where('import_log_id', $id)
            ->where('status', 'draft')
            ->get();

        if ($salesImport->isEmpty()) {
            return redirect()->route('sales.import.show', $id)->with('error', 'Tidak ada transaksi import yang dapat difinalize.');
        }

        // Check all stock first
        foreach ($salesImport as $saleImport) {
            foreach ($saleImport->details as $detail) {
                $productWarehouse = ProductWarehouse::where('product_id', $detail->product_id)
                    ->where('warehouse_id', $saleImport->warehouse_id)
                    ->first();

                if (!$productWarehouse || $productWarehouse->stock < $detail->qty) {
                    $productName = $detail->product->title ?? 'Unknown';
                    return redirect()->route('sales.import.show', $id)
                        ->with('error', "Stok tidak mencukupi untuk produk: {$productName}");
                }
            }
        }

        DB::transaction(function () use ($salesImport, $importLog) {
            foreach ($salesImport as $saleImport) {
                // Create main Sale record
                $sale = Sale::create([
                    'invoice' => $saleImport->invoice,
                    'customer_id' => $saleImport->customer_id,
                    'warehouse_id' => $saleImport->warehouse_id,
                    'user_id' => $saleImport->user_id,
                    'grand_total' => $saleImport->grand_total,
                    'discount' => $saleImport->discount,
                    'discount_type' => $saleImport->discount_type,
                    'discount_percent' => $saleImport->discount_percent,
                    'event_discount' => $saleImport->event_discount,
                    'event_discount_type' => $saleImport->event_discount_type,
                    'event_discount_percent' => $saleImport->event_discount_percent,
                    'shipping_cost' => $saleImport->shipping_cost,
                    'other_cost' => $saleImport->other_cost,
                    'shipping_name' => $saleImport->shipping_name,
                    'shipping_phone' => $saleImport->shipping_phone,
                    'shipping_address' => $saleImport->shipping_address,
                    'sender_name' => $saleImport->sender_name,
                    'sender_phone' => $saleImport->sender_phone,
                    'payment_type' => $saleImport->payment_type,
                    'shipping_type' => $saleImport->shipping_type,
                    'notes' => $saleImport->notes,
                    'status' => 'finalized',
                    'finalized_at' => now(),
                ]);

                $totalProfit = 0;

                foreach ($saleImport->details as $detail) {
                    // Create Sale Detail
                    $sale->details()->create([
                        'product_id' => $detail->product_id,
                        'bundle_id' => $detail->bundle_id,
                        'qty' => $detail->qty,
                        'sell_price' => $detail->sell_price,
                        'discount' => $detail->discount,
                        'weight' => $detail->weight,
                    ]);

                    // Update Stock
                    $product = $detail->product;
                    $productWarehouse = ProductWarehouse::where('product_id', $product->id)
                        ->where('warehouse_id', $saleImport->warehouse_id)
                        ->first();

                    $previous_stock = $productWarehouse->stock;
                    $productWarehouse->stock -= $detail->qty;
                    $productWarehouse->save();

                    // Calculate profit
                    $totalProfit += ($detail->sell_price - $product->buy_price) * $detail->qty;

                    // Log Stock Mutation
                    ProductStock::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $saleImport->warehouse_id,
                        'type' => 'out',
                        'qty' => $detail->qty,
                        'previous_stock' => $previous_stock,
                        'current_stock' => $productWarehouse->stock,
                        'user_id' => auth()->id(),
                        'sale_id' => $sale->id,
                        'note' => 'Sale ' . $sale->invoice . ' finalized (Import)',
                    ]);
                }

                // Add shipping and other costs to profit
                $totalProfit += $saleImport->shipping_cost + $saleImport->other_cost;

                // Create Profit record
                $sale->profits()->create([
                    'total' => $totalProfit,
                ]);

                // Mark import as finalized
                $saleImport->update([
                    'status' => 'finalized',
                    'finalized_at' => now(),
                ]);
            }
        });

        $count = $salesImport->count();
        return redirect()->route('sales.index')->with('success', "{$count} transaksi import berhasil difinalize dan stok telah diupdate.");
    }
}
