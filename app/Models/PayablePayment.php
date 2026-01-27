<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayablePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'purchase_id',
        'supplier_id',
        'payment_date',
        'amount',
        'payment_method',
        'bank_name',
        'bank_account',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer Bank',
        'giro' => 'Giro',
        'cek' => 'Cek',
        'other' => 'Lainnya',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journal()
    {
        return $this->morphOne(Journal::class, 'source');
    }

    public function getPaymentMethodLabelAttribute()
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public static function generateReference()
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        $last = static::where('reference', 'like', "{$prefix}-{$date}-%")
            ->orderBy('reference', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->reference, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$date}-{$newNumber}";
    }
}
