<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class NileCruiseDuration extends Model {
    protected $fillable=['package_id','title','days','nights','direction','departure_city_id','arrival_city_id','departure_day','start_from_price','currency_id','is_default','is_active','sort_order'];
    protected $casts=['days'=>'integer','nights'=>'integer','start_from_price'=>'decimal:2','is_default'=>'boolean','is_active'=>'boolean','sort_order'=>'integer'];
    public function package(): BelongsTo { return $this->belongsTo(Package::class); }
    public function departureCity(): BelongsTo { return $this->belongsTo(City::class,'departure_city_id'); }
    public function arrivalCity(): BelongsTo { return $this->belongsTo(City::class,'arrival_city_id'); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function itineraryDays(): HasMany { return $this->hasMany(NileCruiseItineraryDay::class)->orderBy('sort_order')->orderBy('day_number'); }
    public function seasonPrices(): HasMany { return $this->hasMany(NileCruiseSeasonPrice::class)->orderBy('sort_order'); }
}
