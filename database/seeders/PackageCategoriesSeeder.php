<?php

namespace Database\Seeders;

use App\Models\PackageCategory;
use Illuminate\Database\Seeder;

class PackageCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Main Categories
        $mainCategories = [
            'day_tour' => [
                'name' => ['en' => 'Day Tours', 'ar' => 'رحلات اليوم الواحد'],
                'slug' => 'day-tours',
                'category_type' => 'day_tour',
                'description' => ['en' => 'Day trips and excursions across Egypt cities', 'ar' => 'جولات ورحلات يومية في مختلف مدن مصر'],
                'icon' => 'ti ti-sun',
                'sort_order' => 1,
            ],
            'travel_package' => [
                'name' => ['en' => 'Tour Packages', 'ar' => 'برامج سياحية'],
                'slug' => 'tour-packages',
                'category_type' => 'travel_package',
                'description' => ['en' => 'Multi-day vacation packages across Egypt', 'ar' => 'برامج وباقات سياحية متكاملة لعدة أيام'],
                'icon' => 'ti ti-briefcase',
                'sort_order' => 2,
            ],
            'nile_cruise' => [
                'name' => ['en' => 'Nile Cruises', 'ar' => 'رحلات نيلية'],
                'slug' => 'nile-cruises',
                'category_type' => 'nile_cruise',
                'description' => ['en' => 'Luxury Nile river cruises between Luxor and Aswan', 'ar' => 'رحلات كروز نيلية فاخرة بين الأقصر وأسوان'],
                'icon' => 'ti ti-ship',
                'sort_order' => 3,
            ],
        ];

        $mainCategoryModels = [];
        foreach ($mainCategories as $type => $data) {
            $mainCategoryModels[$type] = PackageCategory::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'parent_id' => null,
                    'name' => $data['name'],
                    'category_type' => $data['category_type'],
                    'description' => $data['description'],
                    'icon' => $data['icon'],
                    'is_active' => true,
                    'sort_order' => $data['sort_order'],
                ]
            );
        }

        // 2. Subcategories for Day Tours
        $dayTourParent = $mainCategoryModels['day_tour'];
        $dayTourSubcategories = [
            [
                'slug' => 'cairo-tours',
                'name' => ['en' => 'Cairo Day Tours', 'ar' => 'رحلات القاهرة'],
                'description' => ['en' => 'Pyramids, Egyptian Museum, Coptic Cairo & Khan El Khalili day tours', 'ar' => 'رحلات الأهرامات والمتحف المصري والقاهرة القديمة وخان الخليلي'],
            ],
            [
                'slug' => 'luxor-tours',
                'name' => ['en' => 'Luxor Day Tours', 'ar' => 'رحلات الأقصر'],
                'description' => ['en' => 'Karnak, Valley of the Kings & Hatshepsut temple day excursions', 'ar' => 'رحلات معبد الكرنك ووادي الملوك ومعبد حتشبسوت'],
            ],
            [
                'slug' => 'aswan-tours',
                'name' => ['en' => 'Aswan Day Tours', 'ar' => 'رحلات أسوان'],
                'description' => ['en' => 'Philae Temple, Abu Simbel & High Dam day trips', 'ar' => 'رحلات معبد فيلة وأبو سمبل والسد العالي'],
            ],
            [
                'slug' => 'alexandria-tours',
                'name' => ['en' => 'Alexandria Day Tours', 'ar' => 'رحلات الإسكندرية'],
                'description' => ['en' => 'Citadel of Qaitbay, Catacombs & Alexandria Library excursions', 'ar' => 'رحلات قلعة قايتباي ومكتبة الإسكندرية ومقابر كوم الشقافة'],
            ],
            [
                'slug' => 'hurghada-tours',
                'name' => ['en' => 'Hurghada Tours', 'ar' => 'رحلات الغردقة'],
                'description' => ['en' => 'Red Sea snorkeling, Giftun Island & desert quad safari tours', 'ar' => 'رحلات السنوركلينج وجزيرة جفتون والسفاري الهوائية في الغردقة'],
            ],
            [
                'slug' => 'sharm-el-sheikh-tours',
                'name' => ['en' => 'Sharm El Sheikh Tours', 'ar' => 'رحلات شرم الشيخ'],
                'description' => ['en' => 'Ras Mohamed snorkeling, Mount Sinai & St Catherine day trips', 'ar' => 'رحلات محمية رأس محمد ودير سانت كاترين وجبل موسى'],
            ],
            [
                'slug' => 'marsa-alam-tours',
                'name' => ['en' => 'Marsa Alam Tours', 'ar' => 'رحلات مرسى علم'],
                'description' => ['en' => 'Dolphin Reef, Sataya & Luxor excursions from Marsa Alam', 'ar' => 'رحلات صطايح وشعاب المرجان والأقصر من مرسى علم'],
            ],
            [
                'slug' => 'dahab-tours',
                'name' => ['en' => 'Dahab Day Tours', 'ar' => 'رحلات دهب'],
                'description' => ['en' => 'Blue Hole, Colored Canyon & Saint Catherine day excursions', 'ar' => 'رحلات البلو هول والأخدود الملون وسانت كاترين'],
            ],
            [
                'slug' => 'egypt-cultural-tours',
                'name' => ['en' => 'Cultural & Historical Tours', 'ar' => 'رحلات ثقافية وتاريخية'],
                'description' => ['en' => 'Guided day tours focusing on Egyptian heritage, museums & monuments', 'ar' => 'رحلات يومية متخصصة في الآثار والتاريخ والتراث المصري'],
            ],
            [
                'slug' => 'desert-safari-tours',
                'name' => ['en' => 'Desert Safari & Quad Tours', 'ar' => 'رحلات سفاري وركوب الدبابات'],
                'description' => ['en' => 'Thrilling desert safari, quad bikes, and Bedouin dinner experiences', 'ar' => 'رحلات سفاري الصحراء وسيارات الدفع الرباعي والسهرات البدوية'],
            ],
        ];

        $sort = 1;
        foreach ($dayTourSubcategories as $sub) {
            PackageCategory::updateOrCreate(
                ['slug' => $sub['slug']],
                [
                    'parent_id' => $dayTourParent->id,
                    'category_type' => 'day_tour',
                    'name' => $sub['name'],
                    'description' => $sub['description'],
                    'is_active' => true,
                    'sort_order' => $sort++,
                ]
            );
        }

        // 3. Subcategories for Tour Packages
        $travelPackageParent = $mainCategoryModels['travel_package'];
        $travelPackageSubcategories = [
            [
                'slug' => 'egypt-vacation-packages',
                'name' => ['en' => 'Egypt Vacation Packages', 'ar' => 'باقات العطلات المصرية'],
                'description' => ['en' => 'Curated multi-day vacation packages across Cairo, Nile & Red Sea', 'ar' => 'برامج عطلات سياحية متكاملة في القاهرة والنيل والبحر الأحمر'],
            ],
            [
                'slug' => 'private-egypt-tours',
                'name' => ['en' => 'Private Egypt Tours', 'ar' => 'رحلات مصر الخاصة'],
                'description' => ['en' => 'Exclusive private tours with dedicated Egyptologist and private vehicle', 'ar' => 'جولات سياحية خاصة مع مرشد وسائق خاص'],
            ],
            [
                'slug' => 'egypt-luxury-tours',
                'name' => ['en' => 'Luxury Egypt Vacations', 'ar' => 'رحلات مصر الفاخرة'],
                'description' => ['en' => '5-star ultra luxury hotels, premier Nile cruises and VIP service', 'ar' => 'برامج فاخرة بفنادق 5 نجوم وكروزات نيلية ممتازة'],
            ],
            [
                'slug' => 'family-egypt-tours',
                'name' => ['en' => 'Family Egypt Tours', 'ar' => 'رحلات العائلات في مصر'],
                'description' => ['en' => 'Family-friendly itineraries with balanced pacing and engaging activities', 'ar' => 'برامج سياحية مخصصة للعائلات وتناسب جميع الأعمار'],
            ],
            [
                'slug' => 'egypt-honeymoon-packages',
                'name' => ['en' => 'Egypt Honeymoon Packages', 'ar' => 'باقات شهر العسل في مصر'],
                'description' => ['en' => 'Romantic holiday packages combining Nile cruises and beach resorts', 'ar' => 'برامج شهر عسل رومانسية تجمع بين النيل ومنتجعات البحر الأحمر'],
            ],
            [
                'slug' => 'egypt-classic-packages',
                'name' => ['en' => 'Egypt Classic Packages', 'ar' => 'برامج مصر الكلاسيكية'],
                'description' => ['en' => 'The ultimate classic highlights of Cairo Pyramids, Luxor & Aswan', 'ar' => 'البرامج الكلاسيكية لأهم معالم مصر بالقاهرة والأقصر وأسوان'],
            ],
            [
                'slug' => 'budget-egypt-packages',
                'name' => ['en' => 'Short Break & Budget Packages', 'ar' => 'برامج اقتصادية وسريعة'],
                'description' => ['en' => 'Short 2 to 5 day city breaks and budget friendly Egypt vacations', 'ar' => 'برامج سياحية سريعة واقتصادية من يومين إلى 5 أيام'],
            ],
            [
                'slug' => 'egypt-jordan-packages',
                'name' => ['en' => 'Egypt & Jordan Packages', 'ar' => 'برامج مصر والأردن'],
                'description' => ['en' => 'Combined multi-country packages covering Egypt Pyramids & Petra Jordan', 'ar' => 'برامج مشتركة تجمع بين معالم مصر والبتراء في الأردن'],
            ],
        ];

        $sort = 1;
        foreach ($travelPackageSubcategories as $sub) {
            PackageCategory::updateOrCreate(
                ['slug' => $sub['slug']],
                [
                    'parent_id' => $travelPackageParent->id,
                    'category_type' => 'travel_package',
                    'name' => $sub['name'],
                    'description' => $sub['description'],
                    'is_active' => true,
                    'sort_order' => $sort++,
                ]
            );
        }
    }
}
