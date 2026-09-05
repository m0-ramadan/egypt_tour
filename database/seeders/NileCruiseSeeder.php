<?php

namespace Database\Seeders;

use App\Models\NileCruiseCategory;
use App\Models\NileCruiseType;
use Illuminate\Database\Seeder;

class NileCruiseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'slug' => 'luxor-aswan-nile-cruises',
                'name' => [
                    'en' => 'Luxor and Aswan Nile Cruises',
                    'ar' => 'رحلات النيل بين الأقصر وأسوان',
                ],
                'short_description' => [
                    'en' => 'Experience classic Nile River cruises between Luxor and Aswan visiting ancient temples and royal tombs.',
                    'ar' => 'استمتع بأروع الرحلات النيلية الكلاسيكية بين الأقصر وأسوان وزيارة المعابد والمعالم الأثرية.',
                ],
                'description' => [
                    'en' => 'Embark on an unforgettable journey along the Nile River between Luxor and Aswan. Discover ancient Egyptian temples, royal tombs, and breathtaking river views on our handpicked Nile cruises.',
                    'ar' => 'رحلات نيلية ساحرة بين الأقصر وأسوان تمنحك فرصة اكتشاف تاريخ مصر القديم وزيارة معالم كباش والكرنك ووادي الملوك وأسوان.',
                ],
                'featured_image' => 'website/images/nile-cruises/luxor-aswan.jpg',
                'sort_order' => 1,
            ],
            [
                'slug' => 'dahabiya-nile-cruise',
                'name' => [
                    'en' => 'Dahabiya Nile Cruise',
                    'ar' => 'رحلات الدهبية النيلية',
                ],
                'short_description' => [
                    'en' => 'Sail the Nile in luxury and privacy on traditional multi-sail Dahabiya yachts.',
                    'ar' => 'أبحر في نهر النيل بخصوصية وفخامة متناهية على متن مراكب الدهبية النيلية الشراعية الأصلية.',
                ],
                'description' => [
                    'en' => 'Dahabiya Nile Cruises offer an exclusive, peaceful, and luxurious sailing experience with small groups, personalized service, and access to unique island stops along the Nile.',
                    'ar' => 'تجربة إبحار فاخرة وهادئة تناسب مجموعات صغيرة أو عائلات يبحثون عن الهدوء والمناظر الطبيعية والخدمة الشخصية على النيل.',
                ],
                'featured_image' => 'website/images/nile-cruises/dahabiya.jpg',
                'sort_order' => 2,
            ],
            [
                'slug' => 'lake-nasser-cruise',
                'name' => [
                    'en' => 'Lake Nasser Cruise',
                    'ar' => 'رحلات بحيرة ناصر',
                ],
                'short_description' => [
                    'en' => 'Explore Nubian heritage and majestic temples of Abu Simbel across Lake Nasser.',
                    'ar' => 'استكشف التراث النوبي الساحر ومعابد معبد أبو سمبل الشهيرة عبر رحلات بحيرة ناصر.',
                ],
                'description' => [
                    'en' => 'Cross Lake Nasser on a serene cruise journey from Aswan to Abu Simbel. Visit remote island temples like Kalabsha, Amada, and the iconic temples of Ramses II.',
                    'ar' => 'رحلة استكشافية فريدة عبر بحيرة ناصر تجعلك تشاهد معالم أبو سمبل وكلابشة وعمداء بأفضل مستوى من الراحة والفخامة.',
                ],
                'featured_image' => 'website/images/nile-cruises/lake-nasser.jpg',
                'sort_order' => 3,
            ],
        ];

        foreach ($types as $typeData) {
            $type = NileCruiseType::updateOrCreate(
                ['slug' => $typeData['slug']],
                $typeData
            );

            if ($type->slug === 'luxor-aswan-nile-cruises') {
                $categories = [
                    [
                        'slug' => 'standard-nile-cruises',
                        'name' => [
                            'en' => 'Standard Nile Cruises',
                            'ar' => 'رحلات نيلية ستاندرد',
                        ],
                        'short_description' => [
                            'en' => 'Comfortable and budget-friendly 5-star Nile river cruise ships.',
                            'ar' => 'رحلات نيلية 5 نجوم مريحة واقتصادية بأسعار مناسبة.',
                        ],
                        'featured_image' => 'website/images/nile-cruises/standard.jpg',
                        'sort_order' => 1,
                    ],
                    [
                        'slug' => 'deluxe-nile-cruises',
                        'name' => [
                            'en' => 'Deluxe Nile Cruises',
                            'ar' => 'رحلات نيلية ديلوكس',
                        ],
                        'short_description' => [
                            'en' => 'Enhanced comfort, elegant cabins, and excellent dining options on the Nile.',
                            'ar' => 'مستوى أعلى من الراحة والغرف الأنيقة والمأكولات المتميزة.',
                        ],
                        'featured_image' => 'website/images/nile-cruises/deluxe.jpg',
                        'sort_order' => 2,
                    ],
                    [
                        'slug' => 'ultra-deluxe-nile-cruises',
                        'name' => [
                            'en' => 'Ultra Deluxe Nile Cruises',
                            'ar' => 'رحلات نيلية ألترا ديلوكس',
                        ],
                        'short_description' => [
                            'en' => 'Premium service, spacious suites, and luxury amenities for discerning travelers.',
                            'ar' => 'خدمة ممتازة وأجنحة واسعة ووسائل راحة راقية للمسافرين المميزين.',
                        ],
                        'featured_image' => 'website/images/nile-cruises/ultra-deluxe.jpg',
                        'sort_order' => 3,
                    ],
                    [
                        'slug' => 'luxury-nile-cruises',
                        'name' => [
                            'en' => 'Luxury Nile Cruises',
                            'ar' => 'رحلات نيلية فاخرة',
                        ],
                        'short_description' => [
                            'en' => 'Top-tier luxury ships, gourmet dining, private butler options, and elite comfort.',
                            'ar' => 'قمة الفخامة والرفاهية، مطاعم فاخرة، وغرف بانورامية على أعلى مستوى.',
                        ],
                        'featured_image' => 'website/images/nile-cruises/luxury.jpg',
                        'sort_order' => 4,
                    ],
                ];

                foreach ($categories as $catData) {
                    NileCruiseCategory::updateOrCreate(
                        ['slug' => $catData['slug']],
                        array_merge($catData, ['nile_cruise_type_id' => $type->id])
                    );
                }
            }
        }
    }
}
