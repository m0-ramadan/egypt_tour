<?php

namespace Database\Seeders;

use App\Models\SocialMedia;
use Illuminate\Database\Seeder;

class SocialMediaSeeder extends Seeder
{
    public function run()
    {
        $socialMedia = [
            ['key' => 'phone',      'value' => '', 'icon' => 'fas fa-phone'],
            ['key' => 'whatsapp',   'value' => '', 'icon' => 'fab fa-whatsapp'],
            ['key' => 'facebook',   'value' => '', 'icon' => 'fab fa-facebook'],
            ['key' => 'twitter',    'value' => '', 'icon' => 'fab fa-twitter'],
            ['key' => 'linkedin',   'value' => '', 'icon' => 'fab fa-linkedin'],
            ['key' => 'instagram',  'value' => '', 'icon' => 'fab fa-instagram'],
            ['key' => 'snapchat',   'value' => '', 'icon' => 'fab fa-snapchat'],
            ['key' => 'telegram',   'value' => '', 'icon' => 'fab fa-telegram'],
            ['key' => 'email',      'value' => '', 'icon' => 'fas fa-envelope'],
            ['key' => 'tiktok',     'value' => '', 'icon' => 'fab fa-tiktok'],
            ['key' => 'youtube',    'value' => '', 'icon' => 'fab fa-youtube'],
            ['key' => 'pinterest',  'value' => '', 'icon' => 'fab fa-pinterest'],
            ['key' => 'imo',        'value' => '', 'icon' => 'fas fa-comment-dots'],
            ['key' => 'discord',    'value' => '', 'icon' => 'fab fa-discord'],
            ['key' => 'threads',    'value' => '', 'icon' => 'fab fa-threads'],
            ['key' => 'reddit',     'value' => '', 'icon' => 'fab fa-reddit'],
            ['key' => 'mastodon',   'value' => '', 'icon' => 'fab fa-mastodon'],
        ];

        foreach ($socialMedia as $item) {
            SocialMedia::create($item);
        }
    }
}
