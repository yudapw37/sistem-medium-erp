<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldBarangPurchase;
use App\Models\OldBarang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class OldBarangPurchaseController extends Controller
{
    public function index()
    {
        $barangPurchases = OldBarangPurchase::when(request()->q, function ($query) {
            $query->where('nama_barang', 'like', '%' . request()->q . '%')
                ->orWhere('code_barang', 'like', '%' . request()->q . '%')
                ->orWhere('nama_barang_master', 'like', '%' . request()->q . '%');
        })->paginate(10);

        return Inertia::render('Dashboard/OldBarangPurchase/Index', [
            'barangPurchases' => $barangPurchases
        ]);
    }

    /**
     * Get list of old_ms_barang for mapping dropdown
     */
    public function getBarangOptions(Request $request)
    {
        $barangs = OldBarang::when($request->q, function ($query) use ($request) {
            $query->where('judul_buku', 'like', '%' . $request->q . '%')
                ->orWhere('id', 'like', '%' . $request->q . '%')
                ->orWhere('barcode', 'like', '%' . $request->q . '%');
        })->limit(20)->get();

        return response()->json($barangs);
    }

    /**
     * Update mapping for old_barang_purchase
     */
    public function updateMapping(Request $request, $id)
    {
        $request->validate([
            'old_barang_id' => 'required|string|exists:old_ms_barang,id',
        ]);

        $oldBarang = OldBarang::findOrFail($request->old_barang_id);

        $barangPurchase = OldBarangPurchase::findOrFail($id);
        $barangPurchase->update([
            'code_barang' => $oldBarang->id,
            'nama_barang_master' => $oldBarang->judul_buku,
        ]);

        // Auto-sync code_barang to all existing old_purchase_details with matching nama
        $synced = DB::table('old_purchase_details')
            ->where('nama', $barangPurchase->nama_barang)
            ->update(['code_barang' => $oldBarang->id]);

        return response()->json([
            'success' => true,
            'message' => "Mapping berhasil disimpan. {$synced} detail purchase diperbarui.",
            'data' => $barangPurchase,
            'synced_details' => $synced,
        ]);
    }

    /**
     * Sync code_barang from mapping to old_purchase_details
     */
    public function syncToPurchaseDetails()
    {
        // Get all mapped barang purchases (where code_barang is not null)
        $mappedPurchases = OldBarangPurchase::whereNotNull('code_barang')
            ->where('code_barang', '!=', '')
            ->get();

        if ($mappedPurchases->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data mapping yang ditemukan.',
                'updated_count' => 0
            ]);
        }

        $updatedCount = 0;

        foreach ($mappedPurchases as $mapping) {
            // Update old_purchase_details where nama matches nama_barang
            $updated = DB::table('old_purchase_details')
                ->where('nama', $mapping->nama_barang)
                ->update(['code_barang' => $mapping->code_barang]);

            $updatedCount += $updated;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyinkronkan {$updatedCount} data.",
            'updated_count' => $updatedCount
        ]);
    }
}
