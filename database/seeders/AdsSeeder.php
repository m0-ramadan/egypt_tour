<?php

namespace Database\Seeders;

use App\Models\Ads;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // قائمة إعلانات ترويجية عامة (مناسبة للصفحة الرئيسية، السلة، المنتجات...)
        $generalAds = [
            ['description' => 'شحن مجاني على كل الطلبات فوق ٣٠٠ جنيه!',       'type' => 'shipping',   'icon' => 'fa-truck-fast'],
            ['description' => 'خصم ١٥٪ على أول طلب بكود: WELCOME15',           'type' => 'discount',   'icon' => 'fa-percent'],
            ['description' => 'اشترِ ٢ منتجات واحصل على الثالث مجانًا!',      'type' => 'buy-get',    'icon' => 'fa-gift'],
            ['description' => 'عروض اليوم فقط: خصم حتى ٢٥٪ على منتجات مختارة', 'type' => 'flash_sale', 'icon' => 'fa-fire'],
            ['description' => 'توصيل سريع خلال ٢٤-٤٨ ساعة + شحن مجاني',       'type' => 'shipping',   'icon' => 'fa-rocket'],
            ['description' => 'الكمية تزيد.. الخصم يزيد! (خصم حتى ٣٠٪)',      'type' => 'quantity',   'icon' => 'fa-boxes-stacked'],
            ['description' => 'هدية مجانية مع كل طلب فوق ٥٠٠ جنيه',            'type' => 'gift',       'icon' => 'fa-star'],
            ['description' => 'خصم خاص للعملاء الجدد ١٢٪ + شحن هدية',         'type' => 'new_customer','icon' => 'fa-user-plus'],
            ['description' => 'اطلب الآن واستمتع بتوصيل مجاني فوري',           'type' => 'shipping',   'icon' => 'fa-bolt'],
            ['description' => 'اشترِ ٤ منتجات واحصل على خصم ٣٠٪ كلي',         'type' => 'quantity',   'icon' => 'fa-percent'],
            ['description' => 'عروض نهاية الأسبوع: خصم ٢٠٪ + هدايا',          'type' => 'weekend',    'icon' => 'fa-calendar-week'],
            ['description' => 'شحن مجاني لجميع المحافظات بدون حد أدنى!',      'type' => 'shipping',   'icon' => 'fa-truck'],
        ];

        $this->command->info('بدء إضافة الإعلانات العامة...');

        DB::transaction(function () use ($generalAds) {
            foreach ($generalAds as $ad) {
                Ads::updateOrCreate(
                    ['description' => $ad['description']], // لتجنب التكرار إذا أعدت التشغيل
                    [
                        'type' => $ad['type'],
                        'icon' => $ad['icon'],
                    ]
                );
            }
        });

        $this->command->info('تم إضافة ' . count($generalAds) . ' إعلان ترويجي عام بنجاح! ✓');
    }
}