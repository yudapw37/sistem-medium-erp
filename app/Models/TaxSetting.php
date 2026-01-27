<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_enabled',
        'default_tax_id',
        'show_tax_on_receipt',
    ];

    protected $casts = [
        'tax_enabled' => 'boolean',
        'show_tax_on_receipt' => 'boolean',
    ];

    /**
     * Get the default tax
     */
    public function defaultTax()
    {
        return $this->belongsTo(Tax::class, 'default_tax_id');
    }

    /**
     * Get global tax settings (singleton pattern)
     */
    public static function getSettings()
    {
        $settings = static::first();
        
        if (!$settings) {
            $settings = static::create([
                'tax_enabled' => false,
                'show_tax_on_receipt' => true,
            ]);
        }

        return $settings;
    }

    /**
     * Check if tax is enabled globally
     */
    public static function isEnabled()
    {
        return static::getSettings()->tax_enabled;
    }

    /**
     * Get the current default tax rate
     */
    public static function getDefaultTax()
    {
        $settings = static::getSettings();
        
        if (!$settings->tax_enabled) {
            return null;
        }

        if ($settings->default_tax_id) {
            return Tax::find($settings->default_tax_id);
        }

        return Tax::getDefault();
    }

    /**
     * Check if tax should be shown on receipt
     */
    public static function showOnReceipt()
    {
        $settings = static::getSettings();
        return $settings->tax_enabled && $settings->show_tax_on_receipt;
    }
}
