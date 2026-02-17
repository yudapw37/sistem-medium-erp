<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class OldOrderController extends Controller
{
    /**
     * Display a listing of old orders.
     */
    public function index(Request $request)
    {
        $orders = OldOrder::with(['customer'])
            ->when($request->q, function ($query, $q) {
                $query->where('id', 'like', '%' . $q . '%');
            })
            ->when($request->start_date, function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($request->end_date, function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Dashboard/OldOrders/Index', [
            'orders' => $orders,
            'filters' => $request->all(['q', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Display the specified old order.
     */
    public function show($id)
    {
        $order = OldOrder::with(['customer', 'details.barang'])->findOrFail($id);

        return Inertia::render('Dashboard/OldOrders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Get orders by date for bulk print selection.
     */
    public function ordersByDate(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        
        $orders = OldOrder::with(['customer'])
            ->whereDate('created_at', $request->date)
            ->get(['id', 'code_customer', 'created_at', 'total_harga']);

        return response()->json($orders);
    }

    /**
     * Print multiple invoices for the specified old orders.
     */
    public function bulkPrint(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        
        $orders = OldOrder::with(['customer', 'details.barang'])
            ->whereIn('id', $request->ids)
            ->get();

        $preparedOrders = [];
        foreach ($orders as $order) {
            $preparedOrders[] = $this->prepareOrderData($order);
        }

        $logoBase64 = $this->getLogoBase64();

        $pdf = Pdf::loadView('old-orders.print', [
            'orders' => $preparedOrders,
            'logo' => $logoBase64
        ]);
        $pdf->setPaper('a4');

        return $pdf->stream('Bulk_Invoices_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Print invoice for the specified old order.
     */
    public function print($id)
    {
        $order = OldOrder::with(['customer', 'details.barang'])->findOrFail($id);
        $data = $this->prepareOrderData($order);

        $filename = 'Invoice_' . $order->id . '_' . ($order->customer->nama ?? 'Customer') . '.pdf';
        
        $logoBase64 = $this->getLogoBase64();

        $pdf = Pdf::loadView('old-orders.print', [
            'orders' => [$data],
            'logo' => $logoBase64
        ]);
        $pdf->setPaper('a4');

        return $pdf->stream($filename);
    }

    /**
     * Helper to get logo as base64
     */
    private function getLogoBase64()
    {
        $path = public_path('assets/logo/logoInv.png');
        if (!file_exists($path)) {
            // Fallback for production with separate app/public folders
            // Try to find it in the current DIR's sibling if public isn't found
            $path = base_path('../public_html/assets/logo/logoInv.png');
            if (!file_exists($path)) {
                return null;
            }
        }
        
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    /**
     * Logic to group and prepare order items (shared between single and bulk print)
     */
    private function prepareOrderData($order)
    {
        $groupedItems = [];
        $promoMap = [];

        foreach ($order->details as $detail) {
            $hasPromo = $detail->nama_promo && $detail->nama_promo !== '-'
                     && $detail->code_promo && $detail->code_promo !== '-';

            if ($hasPromo) {
                $key = $detail->nama_promo;
                if (!isset($promoMap[$key])) {
                    $promoMap[$key] = [
                        'isBundle' => true,
                        'nama_promo' => $detail->nama_promo,
                        'price' => $detail->harga_promo > 0 ? $detail->harga_promo : $detail->harga,
                        'diskon' => $detail->diskon ?? 0,
                        'totalQty' => 0,
                        'totalBerat' => 0,
                        'subtotal' => 0,
                        'children' => [],
                    ];
                    $groupedItems[] = &$promoMap[$key];
                }
                $promoMap[$key]['children'][] = $detail;
                $promoMap[$key]['totalQty'] += $detail->jumlah ?? 0;
                $promoMap[$key]['totalBerat'] += floatval($detail->barang->berat ?? 0) * ($detail->jumlah ?? 0);

                $price = $detail->harga_promo > 0 ? $detail->harga_promo : $detail->harga;
                $sub = $price * ($detail->jumlah ?? 0);
                if ($detail->diskon > 0) {
                    $sub -= ($sub * $detail->diskon / 100);
                }
                $promoMap[$key]['subtotal'] += $sub;
            } else {
                $price = $detail->harga;
                $sub = $price * ($detail->jumlah ?? 0);
                if ($detail->diskon > 0) {
                    $sub -= ($sub * $detail->diskon / 100);
                }
                $groupedItems[] = [
                    'isBundle' => false,
                    'detail' => $detail,
                    'price' => $price,
                    'diskon' => $detail->diskon ?? 0,
                    'totalQty' => $detail->jumlah ?? 0,
                    'totalBerat' => floatval($detail->barang->berat ?? 0) * ($detail->jumlah ?? 0),
                    'subtotal' => $sub,
                ];
            }
        }
        unset($promoMap);

        $grandTotal = ($order->total_harga ?? 0) - ($order->totalDiskon ?? 0) - ($order->diskonKodeUnik ?? 0) + ($order->biayaExpedisi ?? 0);

        return [
            'order' => $order,
            'groupedItems' => $groupedItems,
            'grandTotal' => $grandTotal
        ];
    }
}
