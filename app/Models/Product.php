<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'barcode',
        'title',
        'description',
        'buy_price',
        'sell_price',
        'is_sellable',
        'weight',
        'category_id',
        'stock_min', // Keep this if we added it, or remove if not. User asked for multi-warehouse instead of stock_min. I'll keep it safe.
    ];

    /**
     * category
     *
     * @return void
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * image
     *
     * @return Attribute
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => asset('/storage/products/' . $value),
        );
    }
    /**
     * stockMutations
     *
     * @return void
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouses')
            ->withPivot('stock', 'stock_fix')
            ->withTimestamps();
    }

    /**
     * Get total stock across all warehouses
     */
    public function getStockAttribute()
    {
        return $this->warehouses()->sum('product_warehouses.stock');
    }

    public function stockMutations()
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Get all unit configurations for this product
     */
    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Get the base unit for this product
     */
    public function getBaseUnitAttribute()
    {
        return $this->productUnits()->where('is_base', true)->first();
    }

    /**
     * Find product by any of its unit barcodes
     */
    public static function findByBarcode($barcode)
    {
        // First check product's main barcode
        $product = static::where('barcode', $barcode)->first();
        if ($product) {
            return $product;
        }

        // Then check product_units barcodes
        $productUnit = ProductUnit::where('barcode', $barcode)->with('product')->first();
        if ($productUnit) {
            return $productUnit->product;
        }

        return null;
    }

    /**
     * Get product unit info by barcode
     */
    public static function getUnitInfoByBarcode($barcode)
    {
        // Check main product barcode first
        $product = static::where('barcode', $barcode)->first();
        if ($product) {
            $baseUnit = $product->productUnits()->where('is_base', true)->first();
            return [
                'product' => $product,
                'product_unit' => $baseUnit,
                'conversion_rate' => $baseUnit ? $baseUnit->conversion_rate : 1,
            ];
        }

        // Check product_units barcodes
        $productUnit = ProductUnit::where('barcode', $barcode)->with(['product', 'unit'])->first();
        if ($productUnit) {
            return [
                'product' => $productUnit->product,
                'product_unit' => $productUnit,
                'conversion_rate' => $productUnit->conversion_rate,
            ];
        }

        return null;
    }
}
