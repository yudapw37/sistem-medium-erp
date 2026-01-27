<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'type',
        'qty',
        'previous_stock',
        'current_stock',
        'transaction_id',
        'sale_id',
        'user_id',
        'purchase_id',
        'purchase_return_id',
        'sale_return_id',
        'stock_opname_id',
        'note',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }


    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }
}
