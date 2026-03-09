<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\OldBarang;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OldBarangMasterController extends Controller
{
    public function index()
    {
        $barangs = OldBarang::when(request()->q, function ($query) {
            $query->where('judul_buku', 'like', '%' . request()->q . '%')
                ->orWhere('id', 'like', '%' . request()->q . '%')
                ->orWhere('kategori', 'like', '%' . request()->q . '%')
                ->orWhere('barcode', 'like', '%' . request()->q . '%');
        })->paginate(10);

        return Inertia::render('Dashboard/OldBarangMaster/Index', [
            'barangs' => $barangs
        ]);
    }
}
