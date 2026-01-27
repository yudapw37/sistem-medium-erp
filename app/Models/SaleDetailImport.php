<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetailImport extends Model
{
    use HasFactory;

    protected $table = 'sale_details_import';

    protected $fillable = [
        'sale_import_id',
        'product_id',
        'bundle_id',
        'qty',
        'sell_price',
        'discount',
        'weight',
    ];

    public function saleImport()
    {
        return $this->belongsTo(SaleImport::class, 'sale_import_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function bundle()
    {
        return $this->belongsTo(ProductBundle::class, 'bundle_id');
    }
}
