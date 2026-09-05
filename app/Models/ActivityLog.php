<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = ['admin_id', 'action', 'loggable_type', 'loggable_id', 'description', 'ip_address', 'user_agent'];

    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
}
