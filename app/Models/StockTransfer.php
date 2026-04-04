<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'code',
        'from_warehouse_id',
        'to_warehouse_id',
        'user_id',
        'date',
        'notes',
        'status',
        'finalized_at',
    ];

    protected $casts = [
        'date'         => 'date',
        'finalized_at' => 'datetime',
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(StockTransferDetail::class);
    }

    public static function generateCode(): string
    {
        $prefix = 'MT-' . now()->format('Ymd');
        $last   = static::where('code', 'like', "{$prefix}-%")
            ->orderBy('code', 'desc')
            ->first();

        $next = $last ? ((int) substr($last->code, -4)) + 1 : 1;

        return $prefix . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
