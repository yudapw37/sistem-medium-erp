<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldStockRunning extends Model
{
    use HasFactory;

    protected $table = 'old_stock_running';

    protected $fillable = [
        'code_barang',
        'stock_awal',
        'stock_masuk',
        'stock_keluar',
        'stock_saldo',
    ];

    protected $casts = [
        'stock_awal' => 'integer',
        'stock_masuk' => 'integer',
        'stock_keluar' => 'integer',
        'stock_saldo' => 'integer',
    ];

    public function barang()
    {
        return $this->belongsTo(OldBarang::class, 'code_barang', 'id');
    }

    public function stockAwal()
    {
        return $this->hasOne(OldStockAwal::class, 'code_barang', 'code_barang');
    }
}
