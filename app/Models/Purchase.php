<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice',
        'supplier_id',
        'user_id',
        'grand_total',
        'subtotal',
        'tax_id',
        'tax_amount',
        'tax_rate',
        'status',
        'finalized_at',
        'warehouse_id',
        'notes',
        'payment_type',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function payables()
    {
        return $this->hasMany(PayablePayment::class);
    }
}
