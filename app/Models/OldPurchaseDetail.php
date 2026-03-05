<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldPurchaseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_purchase_id',
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

    public function purchase()
    {
        return $this->belongsTo(OldPurchase::class, 'old_purchase_id');
    }
}
