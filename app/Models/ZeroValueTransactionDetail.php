<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZeroValueTransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'zero_value_transaction_id',
        'product_id',
        'qty',
        'buy_price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'buy_price' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(ZeroValueTransaction::class, 'zero_value_transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalValueAttribute()
    {
        return $this->qty * $this->buy_price;
    }
}
