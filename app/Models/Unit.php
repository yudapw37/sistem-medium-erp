<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get product units using this unit
     */
    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Scope for active units
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
