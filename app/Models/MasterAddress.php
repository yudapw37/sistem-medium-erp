<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterAddress extends Model
{
    protected $fillable = ['province', 'city', 'district', 'postal_code'];
}
