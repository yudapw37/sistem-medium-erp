<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldPurchaseAktif extends Model
{
    use HasFactory;

    protected $table = 'old_purchase_aktif';

    protected $fillable = [
        'old_purchase_id',
        'nomor_faktur',
        'supplier',
        'tanggal_faktur',
        'harga_total',
        'ppn',
        'subtotal',
        'is_final',
        'final_at',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'final_at' => 'datetime',
        'tanggal_faktur' => 'date',
        'harga_total' => 'decimal:2',
        'ppn' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function details()
    {
        return $this->hasMany(OldPurchaseAktifDetail::class, 'old_purchase_aktif_id');
    }

    public function oldPurchase()
    {
        return $this->belongsTo(OldPurchase::class, 'old_purchase_id');
    }
}
