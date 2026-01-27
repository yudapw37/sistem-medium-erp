<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'filename',
        'total_rows',
        'success_count',
        'failed_count',
        'errors',
        'status',
    ];

    protected $casts = [
        'errors' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSuccessRateAttribute()
    {
        if ($this->total_rows === 0) return 0;
        return round(($this->success_count / $this->total_rows) * 100, 1);
    }

    public function salesImport()
    {
        return $this->hasMany(SaleImport::class);
    }
}
