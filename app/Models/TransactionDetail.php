<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;
    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id', 'product_id', 'bundle_id', 'qty', 'price', 'weight'
    ];

    /**
     * transaction
     *
     * @return void
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * product
     *
     * @return void
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * bundle
     *
     * @return void
     */
    public function bundle()
    {
        return $this->belongsTo(ProductBundle::class, 'bundle_id');
    }
}
