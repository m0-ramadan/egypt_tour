<?php
namespace App\Models;
use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class NileCruiseItineraryDay extends Model {
    use HasTranslatableAttributes;
    protected $fillable=['nile_cruise_duration_id','day_number','title','description','meals','overnight','sort_order'];
    protected $casts=['title'=>'array','description'=>'array','meals'=>'array','overnight'=>'array','day_number'=>'integer','sort_order'=>'integer'];
    public function duration(): BelongsTo { return $this->belongsTo(NileCruiseDuration::class,'nile_cruise_duration_id'); }
    public function activities(): HasMany { return $this->hasMany(NileCruiseItineraryActivity::class)->orderBy('sort_order'); }
    public function getDisplayTitleAttribute(): string { return $this->translatedValue('title'); }
    public function getDisplayDescriptionAttribute(): string { return $this->translatedValue('description'); }
    public function getDisplayOvernightAttribute(): string { return $this->translatedValue('overnight'); }
}
