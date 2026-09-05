<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoMediable extends Model
{
    protected $fillable = ['video_id', 'mediable_type', 'mediable_id'];

    public function video(): BelongsTo { return $this->belongsTo(Video::class); }
}
