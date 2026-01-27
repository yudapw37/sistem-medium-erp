<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_id',
        'level',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id')->orderBy('code');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getFullNameAttribute()
    {
        return $this->code . ' - ' . $this->name;
    }

    public function getTypeNameAttribute()
    {
        $types = [
            'asset' => 'Aset',
            'liability' => 'Kewajiban',
            'equity' => 'Ekuitas',
            'revenue' => 'Pendapatan',
            'expense' => 'Beban',
        ];
        return $types[$this->type] ?? $this->type;
    }
}
