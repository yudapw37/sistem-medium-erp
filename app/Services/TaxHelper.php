<?php

namespace App\Services;

use App\Models\Tax;
use App\Models\TaxSetting;

class TaxHelper
{
    /**
     * Check if tax is globally enabled
     */
    public static function isEnabled(): bool
    {
        return TaxSetting::isEnabled();
    }

    /**
     * Get the default tax for sales
     */
    public static function getDefaultTax(): ?Tax
    {
        return TaxSetting::getDefaultTax();
    }

    /**
     * Get tax rate (percentage)
     */
    public static function getRate(): float
    {
        $tax = static::getDefaultTax();
        return $tax ? (float) $tax->rate : 0;
    }

    /**
     * Calculate tax amount from subtotal
     * @param float $subtotal The subtotal amount
     * @param Tax|null $tax Optional specific tax, uses default if null
     * @return float Tax amount
     */
    public static function calculateTax(float $subtotal, ?Tax $tax = null): float
    {
        if (!static::isEnabled()) {
            return 0;
        }

        $tax = $tax ?? static::getDefaultTax();
        
        if (!$tax) {
            return 0;
        }

        return $tax->calculateTax($subtotal);
    }

    /**
     * Get total with tax from subtotal
     */
    public static function getTotalWithTax(float $subtotal, ?Tax $tax = null): float
    {
        if (!static::isEnabled()) {
            return $subtotal;
        }

        $tax = $tax ?? static::getDefaultTax();
        
        if (!$tax) {
            return $subtotal;
        }

        return $tax->getTotal($subtotal);
    }

    /**
     * Get breakdown of amounts
     * @return array ['subtotal', 'tax_amount', 'tax_name', 'tax_rate', 'total']
     */
    public static function getBreakdown(float $subtotal, ?Tax $tax = null): array
    {
        if (!static::isEnabled()) {
            return [
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'tax_name' => null,
                'tax_rate' => 0,
                'total' => $subtotal,
            ];
        }

        $tax = $tax ?? static::getDefaultTax();
        
        if (!$tax) {
            return [
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'tax_name' => null,
                'tax_rate' => 0,
                'total' => $subtotal,
            ];
        }

        $taxAmount = $tax->calculateTax($subtotal);
        $total = $tax->getTotal($subtotal);

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'tax_name' => $tax->name,
            'tax_rate' => $tax->rate,
            'total' => $total,
        ];
    }

    /**
     * Check if tax should be shown on receipt
     */
    public static function showOnReceipt(): bool
    {
        return TaxSetting::showOnReceipt();
    }

    /**
     * Get available taxes for sales
     */
    public static function getForSales()
    {
        return Tax::active()->forSales()->get();
    }

    /**
     * Get available taxes for purchases
     */
    public static function getForPurchases()
    {
        return Tax::active()->forPurchases()->get();
    }
}
