<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldBarang extends Model
{
    protected $table = 'old_ms_barang';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['judul_buku'];
}
