<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'ip',
        'host',
        'method',
        'path',
        'full_url',
        'referer',
        'user_agent',
        'browser',
        'browser_version',
        'platform',
        'device',
        'is_mobile',
        'is_tablet',
        'is_desktop',
        'is_bot',
        'country',
        'country_iso',
        'region',
        'city',
        'latitude',
        'longitude',
        'timezone',
        'headers',
        'query',
        'session_id'
    ];


    protected $casts = [
        'headers' => 'array',
        'query' => 'array',
        'body' => 'array',
        'is_mobile' => 'boolean',
        'is_tablet' => 'boolean',
        'is_desktop' => 'boolean',
        'is_bot' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
