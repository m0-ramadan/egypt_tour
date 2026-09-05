<?php
namespace App\Models;
use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NileCruiseItineraryActivity extends Model {
    use HasTranslatableAttributes;
    protected $fillable=['nile_cruise_itinerary_day_id','attraction_id','title','description','sort_order'];
    protected $casts=['title'=>'array','description'=>'array','sort_order'=>'integer'];
    public function day(): BelongsTo { return $this->belongsTo(NileCruiseItineraryDay::class,'nile_cruise_itinerary_day_id'); }
    public function attraction(): BelongsTo { return $this->belongsTo(Attraction::class); }
    public function getDisplayTitleAttribute(): string { return $this->translatedValue('title'); }
    public function getDisplayDescriptionAttribute(): string { return $this->translatedValue('description'); }
}
