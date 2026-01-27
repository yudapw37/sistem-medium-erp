<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPenyesuaianDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_penyesuaian_id',
        'product_id',
        'qty',
    ];

    public function stockPenyesuaian()
    {
        return $this->belongsTo(StockPenyesuaian::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
