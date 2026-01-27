<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'name', 'no_telp', 'address', 'is_member'
    ];

    /**
     * addresses
     *
     * @return void
     */
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
