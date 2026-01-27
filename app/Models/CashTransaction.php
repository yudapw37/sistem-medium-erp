<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'date',
        'type',
        'account_id',
        'cash_account_id',
        'amount',
        'description',
        'status',
        'finalized_at',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'finalized_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function cashAccount()
    {
        return $this->belongsTo(Account::class, 'cash_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journal()
    {
        return $this->morphOne(Journal::class, 'source');
    }

    /**
     * Check if transaction is draft
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if transaction is finalized
     */
    public function isFinalized()
    {
        return $this->status === 'finalized';
    }

    /**
     * Generate next reference number
     */
    public static function generateReference($type)
    {
        $prefix = $type === 'in' ? 'CIN' : 'COUT';
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

    /**
     * Scope for cash in
     */
    public function scopeCashIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for cash out
     */
    public function scopeCashOut($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope for draft
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for finalized
     */
    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }
}
