<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NileCruiseCabin extends Model {
    protected $fillable=['package_id','name','quantity','bed_type','size_sqm','max_adults','max_children','has_private_bathroom','has_private_terrace','amenities','description','featured_image','sort_order'];
    protected $casts=['quantity'=>'integer','size_sqm'=>'decimal:2','max_adults'=>'integer','max_children'=>'integer','has_private_bathroom'=>'boolean','has_private_terrace'=>'boolean','amenities'=>'array','sort_order'=>'integer'];
    public function package(): BelongsTo { return $this->belongsTo(Package::class); }
}
