<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'rate',
        'type',
        'applies_to',
        'account_id',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the account for this tax
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Calculate tax amount based on type
     */
    public function calculateTax($amount)
    {
        if ($this->type === 'included') {
            // Tax is already included in amount
            return round($amount - ($amount / (1 + ($this->rate / 100))), 2);
        } else {
            // Tax is excluded, add to amount
            return round($amount * ($this->rate / 100), 2);
        }
    }

    /**
     * Get subtotal from total (for included tax)
     */
    public function getSubtotal($total)
    {
        if ($this->type === 'included') {
            return round($total / (1 + ($this->rate / 100)), 2);
        }
        return $total;
    }

    /**
     * Get total from subtotal (for excluded tax)
     */
    public function getTotal($subtotal)
    {
        if ($this->type === 'excluded') {
            return round($subtotal * (1 + ($this->rate / 100)), 2);
        }
        return $subtotal;
    }

    /**
     * Scope for active taxes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for sales taxes
     */
    public function scopeForSales($query)
    {
        return $query->whereIn('applies_to', ['sales', 'both']);
    }

    /**
     * Scope for purchase taxes
     */
    public function scopeForPurchases($query)
    {
        return $query->whereIn('applies_to', ['purchases', 'both']);
    }

    /**
     * Get the default tax
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->where('is_active', true)->first();
    }
}
