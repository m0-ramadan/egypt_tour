<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Communication extends Model
{
    protected $fillable = [
        'client_id',
        'inquiry_id',
        'booking_id',
        'type',
        'direction',
        'subject',
        'content',
        'sent_at',
        'status',
        'attachment_url',
        'created_by',
        'related_type'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
