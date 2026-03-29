<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldStockAwal extends Model
{
    use HasFactory;

    protected $table = 'old_stock_awal';

    protected $fillable = [
        'code_barang',
        'qty',
        'tanggal',
        'is_synced',
        'synced_at',
    ];

    protected $casts = [
        'qty' => 'integer',
        'tanggal' => 'date',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(OldBarang::class, 'code_barang', 'id');
    }

    public function stockRunning()
    {
        return $this->belongsTo(OldStockRunning::class, 'code_barang', 'code_barang');
    }
}
