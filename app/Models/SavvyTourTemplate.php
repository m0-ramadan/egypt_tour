<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavvyTourTemplate extends Model
{
    use HasFactory;

    protected $table = 'savvy_tour_templates';

    protected $fillable = [
        'remote_id',
        'remote_slug',
        'name',
        'description',
        'remote_tour_type',
        'remote_category',
        'region',
        'destinations',
        'cities',
        'vessel_classes',
        'default_ship_slug',
        'duration_value',
        'duration_unit',
        'description_template',
        'highlights',
        'itinerary_outline',
        'includes',
        'excludes',
        'ai_prompt_template',
        'ai_config',
        'customization_options',
        'suggested_min_price',
        'suggested_max_price',
        'price_currency',
        'min_participants',
        'max_participants',
        'difficulty_level',
        'tags',
        'generation_count',
        'popularity_score',
        'remote_is_active',
        'remote_is_featured',
        'remote_sort_order',
        'allowed_plans',
        'remote_created_at',
        'remote_updated_at',
        'last_synced_at',
        'raw_payload',
        'preview_media_id',
        'imported_package_id',
        'import_status',
        'imported_at',
        'last_import_error',
        'missing_from_last_sync',
    ];

    protected $casts = [
        'name' => 'array',
        'destinations' => 'array',
        'cities' => 'array',
        'vessel_classes' => 'array',
        'highlights' => 'array',
        'itinerary_outline' => 'array',
        'includes' => 'array',
        'excludes' => 'array',
        'ai_config' => 'array',
        'customization_options' => 'array',
        'tags' => 'array',
        'allowed_plans' => 'array',
        'raw_payload' => 'array',
        'duration_value' => 'integer',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
        'generation_count' => 'integer',
        'remote_sort_order' => 'integer',
        'suggested_min_price' => 'decimal:2',
        'suggested_max_price' => 'decimal:2',
        'popularity_score' => 'decimal:2',
        'remote_is_active' => 'boolean',
        'remote_is_featured' => 'boolean',
        'missing_from_last_sync' => 'boolean',
        'remote_created_at' => 'datetime',
        'remote_updated_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'imported_at' => 'datetime',
    ];

    public function importedPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'imported_package_id');
    }

    public function previewMedia(): BelongsTo
    {
        return $this->belongsTo(SavvyMedia::class, 'preview_media_id');
    }

    public function getDisplayNameAttribute(): string
    {
        if (is_array($this->name)) {
            return $this->name['en'] ?? $this->name['ar'] ?? reset($this->name) ?: 'Tour Template #' . $this->remote_id;
        }

        return (string) ($this->name ?: 'Tour Template #' . $this->remote_id);
    }

    public function getIsImportedAttribute(): bool
    {
        return $this->import_status === 'imported' || ($this->imported_package_id && $this->importedPackage()->exists());
    }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration_value) {
            return '-';
        }

        $unit = strtolower((string) $this->duration_unit);
        if ($unit === 'hours' || $unit === 'hour') {
            return $this->duration_value . ' ' . ($this->duration_value == 1 ? 'Hour' : 'Hours');
        }

        return $this->duration_value . ' ' . ($this->duration_value == 1 ? 'Day' : 'Days');
    }
}
