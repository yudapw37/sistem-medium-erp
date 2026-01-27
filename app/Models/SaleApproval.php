<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'type',
        'status',
        'user_id',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFinance($query)
    {
        return $query->where('type', 'finance');
    }

    public function scopeWarehouse($query)
    {
        return $query->where('type', 'warehouse');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
