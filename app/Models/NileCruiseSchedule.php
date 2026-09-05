<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NileCruiseSchedule extends Model {
    protected $fillable=['package_id','departure_day','departure_city_id','arrival_city_id','direction','notes','is_active','sort_order'];
    protected $casts=['is_active'=>'boolean','sort_order'=>'integer'];
    public function package(): BelongsTo { return $this->belongsTo(Package::class); }
    public function departureCity(): BelongsTo { return $this->belongsTo(City::class,'departure_city_id'); }
    public function arrivalCity(): BelongsTo { return $this->belongsTo(City::class,'arrival_city_id'); }
}
