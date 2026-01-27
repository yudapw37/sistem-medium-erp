<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_id',
        'cashier_id',
        'customer_id',
        'invoice',
        'cash',
        'change',
        'discount',
        'discount_type',
        'discount_percent',
        'event_discount',
        'event_discount_type',
        'event_discount_percent',
        'subtotal',
        'tax_id',
        'tax_amount',
        'tax_rate',
        'grand_total',
        'payment_method',
        'payment_status',
        'payment_reference',
        'payment_url',
    ];

    /**
     * details
     *
     * @return void
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Tax relationship
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * customer
     *
     * @return void
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * cashier
     *
     * @return void
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * profits
     *
     * @return void
     */
    public function profits()
    {
        return $this->hasMany(Profit::class);
    }

    /**
     * createdAt
     *
     * @return Attribute
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('d-M-Y H:i:s'),
        );
    }
}
