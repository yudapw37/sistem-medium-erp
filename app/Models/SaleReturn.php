<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = [
        'code',
        'Sale_id',
        'Customer_id',
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

    public function Sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function Customer()
    {
        return $this->belongsTo(Customer::class);
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
        return $this->hasMany(SaleReturnDetail::class);
    }
}
