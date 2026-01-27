<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPenyesuaian extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'warehouse_id',
        'date',
        'notes',
        'status',
        'user_id',
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
        return $this->hasMany(StockPenyesuaianDetail::class);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isFinalized()
    {
        return $this->status === 'finalized';
    }
}
