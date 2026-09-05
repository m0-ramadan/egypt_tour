<?php

namespace App\Models;

use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasTranslatableAttributes;

    protected $fillable = [
        'form_id',
        'name',
        'label',
        'field_type',
        'options_json',
        'placeholder',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'label' => 'array',
        'placeholder' => 'array',
        'options_json' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->translatedValue('label');
    }

    public function getDisplayPlaceholderAttribute(): string
    {
        return $this->translatedValue('placeholder');
    }
}
