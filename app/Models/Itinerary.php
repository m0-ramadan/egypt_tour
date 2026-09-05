<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Itinerary extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'package_id',
        'duration',
        'day_number',
        'title',
        'description',
        'meals',
        'meals_breakfast',
        'meals_lunch',
        'meals_dinner',
        'overnight_location',
        'accommodation',
        'transport_notes',
        'activities',
        'start_time',
        'end_time',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'meals' => 'array',
        'overnight_location' => 'array',
        'accommodation' => 'array',
        'transport_notes' => 'array',
        'activities' => 'array',
        'meals_breakfast' => 'boolean',
        'meals_lunch' => 'boolean',
        'meals_dinner' => 'boolean',
        'day_number' => 'integer',
        'sort_order' => 'integer',
    ];

    public function getMealsListAttribute(): array
    {
        if (is_array($this->meals) && !empty($this->meals)) {
            return array_values($this->meals);
        }

        $list = [];
        if ($this->meals_breakfast) {
            $list[] = 'breakfast';
        }
        if ($this->meals_lunch) {
            $list[] = 'lunch';
        }
        if ($this->meals_dinner) {
            $list[] = 'dinner';
        }

        return $list;
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable', 'translatable_type', 'translatable_id');
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->translatedValue('title');
    }
}
