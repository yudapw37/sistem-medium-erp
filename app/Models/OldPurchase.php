<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_faktur',
        'supplier',
        'tanggal_faktur',
        'harga_total',
        'ppn',
        'subtotal',
        'resume_status',
        'pdf_filename',
        'pdf_page',
    ];

    protected $casts = [
        'resume_status' => 'boolean',
        'tanggal_faktur' => 'date',
        'harga_total' => 'decimal:2',
        'ppn' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function details()
    {
        return $this->hasMany(OldPurchaseDetail::class, 'old_purchase_id');
    }
}
