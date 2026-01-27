<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'code',
        'type',
        'warehouse_id',
        'date',
        'notes',
        'user_id',
        'status',
        'finalized_at',
    ];

    protected $casts = [
        'date' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(StockAdjustmentDetail::class);
    }
}
