<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'label',
        'name',
        'phone',
        'address',
        'is_default',
    ];

    /**
     * customer
     *
     * @return void
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
