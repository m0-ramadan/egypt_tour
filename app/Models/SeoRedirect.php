<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoRedirect extends Model
{
    protected $table = 'seo_redirects';

    protected $fillable = ['old_path', 'new_path', 'http_code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
