<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleImport extends Model
{
    use HasFactory;

    protected $table = 'sales_import';

    protected $fillable = [
        'import_log_id',
        'invoice',
        'customer_id',
        'user_id',
        'warehouse_id',
        'grand_total',
        'discount',
        'discount_type',
        'discount_percent',
        'event_discount',
        'event_discount_type',
        'event_discount_percent',
        'shipping_cost',
        'other_cost',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'sender_name',
        'sender_phone',
        'payment_type',
        'shipping_type',
        'status',
        'finalized_at',
        'notes',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
    ];

    public function importLog()
    {
        return $this->belongsTo(ImportLog::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetailImport::class, 'sale_import_id');
    }
}
