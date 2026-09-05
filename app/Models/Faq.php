<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Faq extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'category_id',
        'question',
        'answer',
        'context_type',
        'context_id',
        'is_active',
        'sort_order',
        'is_featured',
    ];

    protected $casts = [
        'question' => 'array',
        'answer' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PackageCategory::class, 'category_id');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable', 'translatable_type', 'translatable_id');
    }

    public function getDisplayQuestionAttribute(): string
    {
        return $this->translatedValue('question');
    }

    public function getDisplayAnswerAttribute(): string
    {
        return $this->translatedValue('answer');
    }
}
