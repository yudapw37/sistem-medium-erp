<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldOrderDetail extends Model
{
    protected $table = 'old_orderdetail';
    public $timestamps = false;

    protected $fillable = [
        'code_order',
        'code_barang',
        'nama_promo',
        'jumlah',
        'Harga',
        'harga_promo',
        'diskon',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'Harga' => 'decimal:2',
        'harga_promo' => 'decimal:2',
        'diskon' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(OldOrder::class, 'code_order', 'id');
    }

    public function barang()
    {
        return $this->belongsTo(OldBarang::class, 'code_barang', 'id');
    }
}
