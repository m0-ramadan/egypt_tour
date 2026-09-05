<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $fillable = ['package_id', 'recommended_package_id', 'sort_order'];

    public function package(): BelongsTo { return $this->belongsTo(Package::class); }
    public function recommendedPackage(): BelongsTo { return $this->belongsTo(Package::class, 'recommended_package_id'); }
}
