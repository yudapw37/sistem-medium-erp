<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldOrder extends Model
{
    protected $table = 'old_order';
    public $incrementing = false;
    protected $keyType = 'string';

    // Prevent Laravel from auto-updating created_at on save()
    // This is critical: old_order uses created_at for monthly grouping,
    // so it must never change after initial insert.
    const CREATED_AT = null;

    protected $fillable = [
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
        'totalDiskon',
        'diskonKodeUnik',
        'biayaExpedisi',
        'resume_status',
    ];

    protected $casts = [
        'total_barang' => 'integer',
        'total_harga' => 'decimal:2',
        'totalDiskon' => 'decimal:2',
        'diskonKodeUnik' => 'decimal:2',
        'biayaExpedisi' => 'decimal:2',
        'resume_status' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(OldCustomer::class, 'code_customer', 'id');
    }

    public function details()
    {
        return $this->hasMany(OldOrderDetail::class, 'code_order', 'id');
    }
}
