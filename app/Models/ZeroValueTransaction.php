<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZeroValueTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'reason',
        'warehouse_id',
        'date',
        'notes',
        'status',
        'user_id',
        'finalized_at',
    ];

    protected $casts = [
        'date' => 'date',
        'finalized_at' => 'datetime',
    ];

    // Reason labels for display
    public const REASON_LABELS = [
        // OUT reasons
        'damaged' => 'Barang Rusak',
        'expired' => 'Kadaluarsa',
        'donation' => 'Hibah/Donasi',
        'loss' => 'Kehilangan',
        // IN reasons
        'bonus' => 'Bonus Supplier',
        'gift' => 'Hadiah',
        'promotion' => 'Promosi',
        'other' => 'Lainnya',
    ];

    public const OUT_REASONS = ['damaged', 'expired', 'donation', 'loss', 'other'];
    public const IN_REASONS = ['bonus', 'gift', 'promotion', 'other'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(ZeroValueTransactionDetail::class);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isFinalized()
    {
        return $this->status === 'finalized';
    }

    public function getReasonLabelAttribute()
    {
        return self::REASON_LABELS[$this->reason] ?? $this->reason;
    }

    public function getTypeLabelAttribute()
    {
        return $this->type === 'in' ? 'Stok Masuk' : 'Stok Keluar';
    }

    public static function generateCode()
    {
        $lastCode = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastCode) {
            $lastNumber = (int) substr($lastCode->code, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'ZVT-' . date('Ymd') . '-' . $newNumber;
    }
}
