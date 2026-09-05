<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'تطريز يدوي',
            'تطريز آلي',
            'طباعة رقمية',
            'طباعة حريرية',
            'تصميم شعارات',
            'ملابس شركات',
            'موضة 2024',
            'خامات عالية الجودة',
            'نصائح العناية',
            'تقنيات حديثة',
            'إبداع تصميم',
            'ألوان متعددة',
            'تطريز ثلاثي الأبعاد',
            'طباعة مستدامة',
            'تصميم جرافيك'
        ];

        foreach ($tags as $tagName) {
            Tag::create([
                'name' => $tagName,
                'slug' => Str::slug($tagName)
            ]);
        }
    }
}
