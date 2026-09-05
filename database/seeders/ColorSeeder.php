<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ColorSeeder extends Seeder
{
    public function run()
    {
        $colors = [
            ['name' => 'أبيض', 'hex_code' => '#FFFFFF'],
            ['name' => 'أسود', 'hex_code' => '#000000'],
            ['name' => 'بني', 'hex_code' => '#8B4513'],
            ['name' => 'بيج', 'hex_code' => '#F5F5DC'],
            ['name' => 'رمادي', 'hex_code' => '#808080'],
            ['name' => 'أزرق', 'hex_code' => '#0000FF'],
            ['name' => 'أخضر', 'hex_code' => '#008000'],
            ['name' => 'أحمر', 'hex_code' => '#FF0000'],
            ['name' => 'ذهبي', 'hex_code' => '#FFD700'],
            ['name' => 'فضي', 'hex_code' => '#C0C0C0'],
        ];

        foreach ($colors as $color) {
            Color::create($color);
        }
    }
}