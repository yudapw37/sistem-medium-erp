<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldOrderAktif extends Model
{
    use HasFactory;

    protected $table = 'old_order_aktif';

    protected $fillable = [
        'old_order_id',
        'code_customer',
        'nama_pengirim',
        'telephone_pengirim',
        'nama_penerima',
        'telephone_penerima',
        'alamat',
        'kecamatan',
        'kab_kota',
        'total_barang',
        'total_harga',
        'total_diskon',
        'diskon_kode_unik',
        'biaya_expedisi',
        'is_final',
        'final_at',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'final_at' => 'datetime',
        'total_barang' => 'integer',
        'total_harga' => 'decimal:2',
        'total_diskon' => 'decimal:2',
        'diskon_kode_unik' => 'decimal:2',
        'biaya_expedisi' => 'decimal:2',
    ];

    public function details()
    {
        return $this->hasMany(OldOrderAktifDetail::class, 'old_order_aktif_id');
    }

    public function oldOrder()
    {
        return $this->belongsTo(OldOrder::class, 'old_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(OldCustomer::class, 'code_customer', 'kode');
    }
}
