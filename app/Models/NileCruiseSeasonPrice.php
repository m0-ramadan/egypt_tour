<?php
namespace App\Models;
use App\Traits\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class NileCruiseSeasonPrice extends Model {
    use HasTranslatableAttributes;
    protected $fillable=['package_id','nile_cruise_duration_id','season_name','date_from','date_to','currency_id','notes','is_active','sort_order'];
    protected $casts=['season_name'=>'array','notes'=>'array','date_from'=>'date','date_to'=>'date','is_active'=>'boolean','sort_order'=>'integer'];
    public function package(): BelongsTo { return $this->belongsTo(Package::class); }
    public function duration(): BelongsTo { return $this->belongsTo(NileCruiseDuration::class,'nile_cruise_duration_id'); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function items(): HasMany { return $this->hasMany(NileCruiseSeasonPriceItem::class)->orderBy('sort_order'); }
    public function getDisplaySeasonNameAttribute(): string { return $this->translatedValue('season_name'); }
    public function getDisplayNotesAttribute(): string { return $this->translatedValue('notes'); }
}
