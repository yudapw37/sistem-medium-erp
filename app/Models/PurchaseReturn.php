<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'code',
        'purchase_id',
        'supplier_id',
        'warehouse_id',
        'user_id',
        'date',
        'grand_total',
        'notes',
        'status',
        'finalized_at',
    ];

    protected $casts = [
        'date' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

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
        return $this->hasMany(PurchaseReturnDetail::class);
    }
}
