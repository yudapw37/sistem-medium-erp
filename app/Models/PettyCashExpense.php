<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'petty_cash_id',
        'date',
        'category',
        'amount',
        'description',
        'receipt_number',
        'status',
        'user_id',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Default expense categories
    const CATEGORIES = [
        'transport' => 'Transportasi',
        'atk' => 'ATK (Alat Tulis Kantor)',
        'listrik' => 'Listrik',
        'air' => 'Air (PDAM)',
        'telepon' => 'Telepon/Internet',
        'konsumsi' => 'Konsumsi',
        'parkir' => 'Parkir',
        'foto_copy' => 'Foto Copy',
        'materai' => 'Materai',
        'kebersihan' => 'Kebersihan',
        'perlengkapan' => 'Perlengkapan',
        'lainnya' => 'Lainnya',
    ];

    public function pettyCash()
    {
        return $this->belongsTo(PettyCash::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute()
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Check if expense is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if expense is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Generate next reference number
     */
    public static function generateReference()
    {
        $prefix = 'PCE';
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
