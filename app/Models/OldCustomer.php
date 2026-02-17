<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldCustomer extends Model
{
    protected $table = 'old_ms_customer';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['nama'];

    public function orders()
    {
        return $this->hasMany(OldOrder::class, 'code_customer', 'id');
    }
}
