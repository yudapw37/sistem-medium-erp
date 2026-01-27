<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'acquisition_date',
        'acquisition_cost',
        'useful_life',
        'salvage_value',
        'depreciation_method',
        'accumulated_depreciation',
        'book_value',
        'status',
        'is_finalized',
        'acquisition_journal_id',
        'disposal_date',
        'disposal_value',
        'asset_account_id',
        'depreciation_account_id',
        'accumulated_depreciation_account_id',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'disposal_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
        'disposal_value' => 'decimal:2',
        'is_finalized' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function depreciations()
    {
        return $this->hasMany(FixedAssetDepreciation::class);
    }

    public function assetAccount()
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function depreciationAccount()
    {
        return $this->belongsTo(Account::class, 'depreciation_account_id');
    }

    public function accumulatedDepreciationAccount()
    {
        return $this->belongsTo(Account::class, 'accumulated_depreciation_account_id');
    }

    /**
     * Calculate monthly depreciation amount
     */
    public function calculateMonthlyDepreciation()
    {
        if ($this->depreciation_method === 'straight_line') {
            $depreciableAmount = $this->acquisition_cost - $this->salvage_value;
            $monthlyDepreciation = $depreciableAmount / ($this->useful_life * 12);
            return round($monthlyDepreciation, 2);
        }

        // Add declining balance method if needed
        return 0;
    }

    /**
     * Update book value
     */
    public function updateBookValue()
    {
        $this->book_value = $this->acquisition_cost - $this->accumulated_depreciation;
        $this->save();
    }
}
