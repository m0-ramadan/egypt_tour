<?php
namespace App\Models;
use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NileCruiseSeasonPriceItem extends Model {
    use HasTranslatableAttributes;
    protected $fillable=['nile_cruise_season_price_id','nile_cruise_cabin_id','occupancy_type','label','price','sort_order'];
    protected $casts=['label'=>'array','price'=>'decimal:2','sort_order'=>'integer'];
    public function seasonPrice(): BelongsTo { return $this->belongsTo(NileCruiseSeasonPrice::class,'nile_cruise_season_price_id'); }
    public function cabin(): BelongsTo { return $this->belongsTo(NileCruiseCabin::class,'nile_cruise_cabin_id'); }
    public function getDisplayLabelAttribute(): string { return $this->translatedValue('label'); }
}
