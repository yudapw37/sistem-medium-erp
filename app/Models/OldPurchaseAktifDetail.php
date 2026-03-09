<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldPurchaseAktifDetail extends Model
{
    use HasFactory;

    protected $table = 'old_purchase_aktif_details';

    protected $fillable = [
        'old_purchase_aktif_id',
        'code_barang',
        'nama',
        'qty',
        'harga_satuan',
        'total',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga_satuan' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function purchaseAktif()
    {
        return $this->belongsTo(OldPurchaseAktif::class, 'old_purchase_aktif_id');
    }

    public function barang()
    {
        return $this->belongsTo(OldBarang::class, 'code_barang', 'id');
    }
}
