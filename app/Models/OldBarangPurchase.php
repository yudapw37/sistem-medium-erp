<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldBarangPurchase extends Model
{
    protected $table = 'old_ms_barang_purchase';
    public $timestamps = false;

    protected $fillable = ['nama_barang', 'code_barang', 'nama_barang_master'];
}
