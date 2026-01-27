<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnDetail extends Model
{
    protected $fillable = [
        'sale_return_id',
        'product_id',
        'qty',
        'sell_price',
    ];

    public function SaleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
