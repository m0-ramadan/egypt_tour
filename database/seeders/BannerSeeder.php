<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * 1️⃣ Banner Types
         */
        $bannerTypes = [
            ['name' => 'main_slider', 'description' => 'Main homepage slider'],
            ['name' => 'image_card', 'description' => 'Small banners / cards'],
            ['name' => 'category_slider', 'description' => 'Slider for categories'],
            ['name' => 'product_slider', 'description' => 'Product slider'],
            ['name' => 'blocks_slider', 'description' => 'Blocks of images'],
        ];

        DB::table('banner_types')->insert($bannerTypes);

        $mainSliderTypeId = DB::table('banner_types')->where('name', 'main_slider')->value('id');
        $imageCardTypeId = DB::table('banner_types')->where('name', 'image_card')->value('id');

        /**
         * 2️⃣ Main Banner (Slider)
         */
        $sliderBannerId = DB::table('banners')->insertGetId([
            'title' => 'Main Homepage Slider',
            'banner_type_id' => $mainSliderTypeId,
            'section_order' => 1,
            'is_active' => true,
        ]);

        /**
         * 3️⃣ Slider Settings
         */
        DB::table('slider_settings')->insert([
            'banner_id' => $sliderBannerId,
            'autoplay' => true,
            'autoplay_delay' => 5000,
            'loop' => true,
            'show_navigation' => true,
            'show_pagination' => true,
            'slides_per_view' => 1,
            'space_between' => 0,
            'breakpoints' => json_encode([
                '640' => ['slidesPerView' => 1],
                '1024' => ['slidesPerView' => 1],
            ]),
        ]);

        /**
         * 4️⃣ Add Slider Items
         */
        DB::table('banner_items')->insert([
            [
                'banner_id' => $sliderBannerId,
                'item_order' => 1,
                'image_url' => 'https://sa.homzmart.net/mageplaza/bannerslider/banner/image/m/a/main_banner-ar-dt_14.jpg',
                'image_alt' => 'Main Slider Image',
                'mobile_image_url' => null,
                'link_url' => '#',
                'is_active' => true
            ]
        ]);

        /**
         * 5️⃣ Small Banner
         */
        $smallBannerId = DB::table('banners')->insertGetId([
            'title' => 'Small Banner Card',
            'banner_type_id' => $imageCardTypeId,
            'section_order' => 2,
            'is_active' => true,
        ]);

        DB::table('banner_items')->insert([
            [
                'banner_id' => $smallBannerId,
                'item_order' => 1,
                'image_url' => 'https://sa.homzmart.net/home_page_builder/KSA-Small_Banner-AR_copy_4-1_8.jpg',
                'image_alt' => 'Small Banner',
                'link_url' => '#',
                'is_active' => true
            ]
        ]);
    }
}
