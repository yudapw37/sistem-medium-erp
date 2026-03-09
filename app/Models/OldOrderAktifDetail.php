<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldOrderAktifDetail extends Model
{
    use HasFactory;

    protected $table = 'old_order_aktif_details';

    protected $fillable = [
        'old_order_aktif_id',
        'code_order',
        'code_barang',
        'nama_promo',
        'jumlah',
        'harga',
        'harga_promo',
        'diskon',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga' => 'decimal:2',
        'harga_promo' => 'decimal:2',
        'diskon' => 'decimal:2',
    ];

    public function orderAktif()
    {
        return $this->belongsTo(OldOrderAktif::class, 'old_order_aktif_id');
    }

    public function barang()
    {
        return $this->belongsTo(OldBarang::class, 'code_barang', 'id');
    }
}
