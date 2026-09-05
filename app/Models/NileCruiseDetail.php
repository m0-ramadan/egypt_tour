<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NileCruiseDetail extends Model
{
    protected $fillable = [
        'package_id',
        'decks',
        'sun_beds',
        'sun_deck_pergolas',
        'tour_style',
        'route_summary',
        'all_inclusive',
        'what_to_bring',
        'on_tour_languages',
        'operating_days',
        'promotional_videos',
        'timezone',
        'deposit_policy',
        'deposit_type',
        'deposit_value',
        'allowed_payment_method_ids',
        'focus_keyword',
        'meta_keywords',
        'og_title',
        'og_description',
        'social_image_path',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'robots_index',
        'robots_follow',
        'pickup_notes',
        'dropoff_notes',
        'fact_sheet_path',
        'additional_notes',
    ];

    protected $casts = [
        'decks' => 'integer',
        'sun_beds' => 'integer',
        'sun_deck_pergolas' => 'integer',
        'all_inclusive' => 'boolean',
        'what_to_bring' => 'array',
        'on_tour_languages' => 'array',
        'operating_days' => 'array',
        'promotional_videos' => 'array',
        'deposit_value' => 'decimal:2',
        'allowed_payment_method_ids' => 'array',
        'meta_keywords' => 'array',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
