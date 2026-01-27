<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'account_id',
        'description',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get account by key
     */
    public static function getAccount($key)
    {
        $setting = static::where('key', $key)->first();
        return $setting?->account;
    }

    /**
     * Get account ID by key
     */
    public static function getAccountId($key)
    {
        $setting = static::where('key', $key)->first();
        return $setting?->account_id;
    }
}
