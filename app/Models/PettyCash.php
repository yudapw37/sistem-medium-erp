<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'date',
        'type',
        'amount',
        'balance',
        'description',
        'status',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expenses()
    {
        return $this->hasMany(PettyCashExpense::class);
    }

    public function journal()
    {
        return $this->morphOne(Journal::class, 'source');
    }

    /**
     * Get active petty cash period
     */
    public static function getActive()
    {
        return static::where('status', 'active')->latest('date')->first();
    }

    /**
     * Check if period is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Calculate remaining balance
     */
    public function getRemainingBalance()
    {
        $totalExpenses = $this->expenses()
            ->where('status', 'approved')
            ->sum('amount');
        
        return $this->amount - $totalExpenses;
    }

    /**
     * Generate next reference number
     */
    public static function generateReference($type)
    {
        $prefix = match($type) {
            'open' => 'PC-OPEN',
            'replenish' => 'PC-REP',
            'close' => 'PC-CLOSE',
            default => 'PC',
        };
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
