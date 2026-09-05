<?php

namespace App\Models;

use App\Models\Wallet\LedgerEntry;
use App\Models\Wallet\UserWallet;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'facebook_id',
        'apple_id',
        'phone',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function notifications()
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable');
    }


    /**
     * Check if user can transact
     */
    public function canTransact(float $amount, string $type = 'withdrawal'): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $wallet = $this->wallet();

        if (! $wallet || $wallet->status !== 'active') {
            return false;
        }

        return true;
    }
}
