<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'الأثاث',
                'children' => [
                    'غرف نوم',
                    'غرف معيشة',
                    'غرف سفرة',
                    'كنب وأرائك',
                    'طاولات',
                    'كراسي'
                ]
            ],
            [
                'name' => 'وحدات تخزين',
                'children' => [
                    'دواليب',
                    'خزائن',
                    'أرفف',
                    'كومودينو',
                    'وحدات أدراج',
                    'منظمات'
                ]
            ],
            [
                'name' => 'ديكورات منزلية',
                'children' => [
                    'لوحات فنية',
                    'مرايا',
                    'ساعات حائط',
                    'مزهريات',
                    'شموع',
                    'تحف ديكورية'
                ]
            ],
            [
                'name' => 'أثاث مكتبي',
                'children' => [
                    'مكاتب',
                    'كراسي مكتبية',
                    'مكتبات',
                    'وحدات تخزين مكتبية',
                    'طاولات اجتماعات',
                    'أكسسوارات مكتبية'
                ]
            ],
            [
                'name' => 'الإضاءة',
                'children' => [
                    'ثريات',
                    'لمبات معلقة',
                    'أباجورات',
                    'إضاءة حائط',
                    'إضاءة أرضية',
                    'إضاءة ديكورية'
                ]
            ],
            [
                'name' => 'أقمشة ومفروشات',
                'children' => [
                    'ستائر',
                    'سجاد',
                    'مفارش سرير',
                    'وسائد',
                    'بطانيات',
                    'مفارش طاولات'
                ]
            ],
            [
                'name' => 'المطبخ والحمام',
                'children' => [
                    'خزائن مطبخ',
                    'طاولات مطبخ',
                    'كراسي مطبخ',
                    'دواليب حمام',
                    'أدوات مطبخ',
                    'أكسسوارات حمام'
                ]
            ],
            [
                'name' => 'الأجهزة المنزلية',
                'children' => [
                    'أجهزة مطبخ صغيرة',
                    'مكانس كهربائية',
                    'مكاوي',
                    'مراوح',
                    'سخانات',
                    'أجهزة تنقية'
                ]
            ],
            [
                'name' => 'أدوات رياضية',
                'children' => [
                    'أجهزة رياضية',
                    'أثقال',
                    'سجادات يوغا',
                    'دراجات رياضية',
                    'معدات اللياقة',
                    'كرات رياضية'
                ]
            ],
            [
                'name' => 'إلكترونيات',
                'children' => [
                    'تلفزيونات',
                    'سماعات',
                    'أجهزة صوتية',
                    'كاميرات',
                    'ألعاب إلكترونية',
                    'أكسسوارات إلكترونية'
                ]
            ],
            [
                'name' => 'أثاث خارجي',
                'children' => [
                    'طاولات خارجية',
                    'كراسي حدائق',
                    'مظلات',
                    'أراجيح',
                    'أرائك خارجية',
                    'إضاءة خارجية'
                ]
            ]
        ];

        $order = 1;
        foreach ($categories as $categoryData) {
            $parent = Category::create([
                'name' => $categoryData['name'],
                'description' => 'قسم ' . $categoryData['name'],
                'order' => $order++
            ]);

            $childOrder = 1;
            foreach ($categoryData['children'] as $childName) {
                Category::create([
                    'name' => $childName,
                    'description' => 'قسم فرعي: ' . $childName,
                    'parent_id' => $parent->id,
                    'order' => $childOrder++
                ]);
            }
        }
    }
}
