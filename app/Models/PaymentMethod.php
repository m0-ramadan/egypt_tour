<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LogicException;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'provider',
        'image',
        'description',
        'config',
        'currency_code',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'icon_url',
        'type',
        'key',
        'icon',
        'is_payment',
        'is_wallet',
    ];

    protected static function booted(): void
    {
        static::deleting(function (PaymentMethod $method): void {
            if ($method->payments()->exists()) {
                throw new LogicException('A payment method used by payment history cannot be deleted.');
            }
        });
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return (string) ($this->name ?? '');
    }

    public function getIconUrlAttribute(): string
    {
        if ($this->image) {
            return Storage::disk('public')->url('payment-methods/' . $this->image);
        }

        return asset('images/default-payment.png');
    }

    public function getTypeAttribute(): ?string
    {
        return $this->provider;
    }

    public function setTypeAttribute(?string $value): void
    {
        $this->attributes['provider'] = $value;
    }

    public function getKeyAttribute(): ?string
    {
        return $this->code;
    }

    public function setKeyAttribute(?string $value): void
    {
        $this->attributes['code'] = $value;
    }

    public function getIconAttribute(): ?string
    {
        return $this->image;
    }

    public function setIconAttribute(?string $value): void
    {
        $this->attributes['image'] = $value;
    }

    public function getIsPaymentAttribute(): bool
    {
        return true;
    }

    public function getIsWalletAttribute(): bool
    {
        return strtolower((string) $this->provider) === 'wallet';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePaymentOnly($query)
    {
        return $query;
    }

    public function scopeOtherOnly($query)
    {
        return $query->whereRaw('1 = 0');
    }

    public static function generateUniqueCode(string $name): string
    {
        $base = Str::slug($name, '-') ?: 'payment-method';
        $code = $base;
        $counter = 1;

        while (self::where('code', $code)->exists()) {
            $code = $base . '-' . $counter++;
        }

        return $code;
    }

    public static function generateUniqueKey(string $name): string
    {
        return self::generateUniqueCode($name);
    }
}
