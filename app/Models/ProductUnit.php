<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_id',
        'conversion_rate',
        'barcode',
        'sell_price',
        'buy_price',
        'is_base',
        'is_default',
    ];

    protected $casts = [
        'conversion_rate' => 'decimal:4',
        'sell_price' => 'integer',
        'buy_price' => 'integer',
        'is_base' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Convert quantity to base unit
     * Example: 2 boxes with conversion_rate 24 = 48 pcs
     */
    public function toBaseQuantity($qty)
    {
        return $qty * $this->conversion_rate;
    }

    /**
     * Convert base quantity to this unit
     * Example: 48 pcs with conversion_rate 24 = 2 boxes
     */
    public function fromBaseQuantity($baseQty)
    {
        return $baseQty / $this->conversion_rate;
    }

    /**
     * Get unit name with conversion info
     */
    public function getDisplayNameAttribute()
    {
        $name = $this->unit->name;
        if (!$this->is_base && $this->conversion_rate > 1) {
            $name .= ' (' . (int)$this->conversion_rate . ' pcs)';
        }
        return $name;
    }
}
