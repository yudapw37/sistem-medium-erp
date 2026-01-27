<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'sell_price',
        'weight',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(ProductBundleItem::class, 'bundle_id');
    }

    public function sales()
    {
        return $this->hasMany(SaleDetail::class, 'bundle_id');
    }

    // Scope for active bundles
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getStockForWarehouse($warehouseId)
    {
        if ($this->items->isEmpty()) {
            return 0;
        }

        $minStock = PHP_INT_MAX;
        
        foreach ($this->items as $item) {
            $pw = ProductWarehouse::where('product_id', $item->product_id)
                ->where('warehouse_id', $warehouseId)
                ->first();
            
            $productStock = $pw ? $pw->stock : 0;
            $possibleBundles = floor($productStock / $item->qty);
            
            if ($possibleBundles < $minStock) {
                $minStock = $possibleBundles;
            }
        }

        return $minStock === PHP_INT_MAX ? 0 : $minStock;
    }

    // Calculate available stock based on component products (General)
    public function getAvailableStockAttribute()
    {
        if ($this->items->isEmpty()) {
            return 0;
        }

        $minStock = PHP_INT_MAX;
        
        foreach ($this->items as $item) {
            $productStock = $item->product ? $item->product->stock : 0;
            $possibleBundles = floor($productStock / $item->qty);
            
            if ($possibleBundles < $minStock) {
                $minStock = $possibleBundles;
            }
        }

        return $minStock === PHP_INT_MAX ? 0 : $minStock;
    }

    /**
     * Calculate total weight from bundle items
     */
    public function getCalculatedWeightAttribute()
    {
        if ($this->items->isEmpty()) {
            return 0;
        }

        $totalWeight = 0;
        foreach ($this->items as $item) {
            $productWeight = $item->product?->weight ?? 0;
            $totalWeight += $productWeight * $item->qty;
        }

        return round($totalWeight, 2);
    }
}
