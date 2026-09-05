<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'flag',
        'is_active',
        'sort_order',
        'is_featured',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    public function packageCategories(): HasMany
    {
        return $this->hasMany(PackageCategory::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'primary_country_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function newsletterSubscribers(): HasMany
    {
        return $this->hasMany(NewsletterSubscriber::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translatedValue('name');
    }
}
