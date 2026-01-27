<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'reference',
        'description',
        'total_debit',
        'total_credit',
        'source_type',
        'source_id',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function entries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Generate next reference number
     */
    public static function generateReference($prefix = 'JRN')
    {
        $date = now()->format('Ymd');
        $lastJournal = static::where('reference', 'like', "{$prefix}-{$date}-%")
            ->orderBy('reference', 'desc')
            ->first();

        if ($lastJournal) {
            $lastNumber = (int) substr($lastJournal->reference, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$date}-{$newNumber}";
    }

    /**
     * Check if journal is balanced
     */
    public function isBalanced()
    {
        return $this->total_debit == $this->total_credit;
    }

    /**
     * Recalculate totals from entries
     */
    public function recalculateTotals()
    {
        $this->total_debit = $this->entries()->sum('debit');
        $this->total_credit = $this->entries()->sum('credit');
        $this->save();
    }
}
