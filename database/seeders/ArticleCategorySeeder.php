<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'تطريز',
                'description' => 'مقالات ونصائح حول فن التطريز ورسومات الزخرفة على الملابس',
                'image' => 'categories/embroidery.jpg',
                'is_active' => true,
                'order' => 1
            ],
            [
                'name' => 'طباعة',
                'description' => 'أحدث تقنيات الطباعة على الملابس والمنسوجات',
                'image' => 'categories/printing.jpg',
                'is_active' => true,
                'order' => 2
            ],
            [
                'name' => 'تصميم',
                'description' => 'نصائح وإرشادات لتصميم شعارات ورسومات للملابس',
                'image' => 'categories/design.jpg',
                'is_active' => true,
                'order' => 3
            ],
            [
                'name' => 'أزياء وموضة',
                'description' => 'آخر صيحات الموضة في عالم الملابس المطبوعة والمطرزة',
                'image' => 'categories/fashion.jpg',
                'is_active' => true,
                'order' => 4
            ],
            [
                'name' => 'نصائح وإرشادات',
                'description' => 'نصائح عملية للعناية بالملابس المطبوعة والمطرزة',
                'image' => 'categories/tips.jpg',
                'is_active' => true,
                'order' => 5
            ]
        ];

        foreach ($categories as $category) {
            ArticleCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'image' => $category['image'],
                'meta_title' => $category['name'] . ' | مدونة متجر التطريز والطباعة',
                'meta_description' => $category['description'],
                'is_active' => $category['is_active'],
                'order' => $category['order']
            ]);
        }

        // إضافة أقسام فرعية
        $embroideryCategory = ArticleCategory::where('slug', 'tatreez')->first();

        if ($embroideryCategory) {
            $subCategories = [
                [
                    'name' => 'تطريز يدوي',
                    'description' => 'فن التطريز التقليدي اليدوي',
                    'parent_id' => $embroideryCategory->id,
                    'order' => 1
                ],
                [
                    'name' => 'تطريز آلي',
                    'description' => 'تقنيات التطريز باستخدام الآلات الحديثة',
                    'parent_id' => $embroideryCategory->id,
                    'order' => 2
                ]
            ];

            foreach ($subCategories as $subCategory) {
                ArticleCategory::create([
                    'name' => $subCategory['name'],
                    'slug' => Str::slug($subCategory['name']),
                    'description' => $subCategory['description'],
                    'parent_id' => $subCategory['parent_id'],
                    'meta_title' => $subCategory['name'] . ' | مدونة متجر التطريز والطباعة',
                    'meta_description' => $subCategory['description'],
                    'is_active' => true,
                    'order' => $subCategory['order']
                ]);
            }
        }
    }
}
