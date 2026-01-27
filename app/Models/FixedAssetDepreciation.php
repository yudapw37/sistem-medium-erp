<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FixedAssetDepreciation extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixed_asset_id',
        'period_date',
        'depreciation_amount',
        'accumulated_depreciation',
        'book_value',
        'journal_id',
    ];

    protected $casts = [
        'period_date' => 'date',
        'depreciation_amount' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}
