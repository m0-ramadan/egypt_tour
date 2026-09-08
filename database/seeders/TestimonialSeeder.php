<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'customer_name' => 'Sarah Jenkins',
                'customer_initials' => 'SJ',
                'rating' => 5,
                'content' => [
                    'en' => 'Unforgettable 7-day trip across Cairo, Luxor, and Aswan. The private Egyptologist was phenomenal, and the Nile Cruise was pure 5-star luxury from start to finish. Everything ran seamlessly!',
                    'ar' => 'رحلة لا تُنسى لمدة 7 أيام بين القاهرة والأقصر وأسوان. المرشد كان استثنائياً ومركب النايل كروز كان في قمة الفخامة من البداية للنهاية. كل شيء تم تنظيمه باحترافية عالية!',
                ],
                'is_verified' => true,
                'is_featured' => true,
                'is_active' => true,
                'source' => 'tripadvisor',
                'source_url' => 'https://www.tripadvisor.com',
                'published_at' => now()->subDays(2),
                'sort_order' => 1,
            ],
            [
                'customer_name' => 'David Miller',
                'customer_initials' => 'DM',
                'rating' => 5,
                'content' => [
                    'en' => 'From the moment we landed in Cairo until our departure from Luxor, Egypt Tour Pro took care of every detail. The private tour of the Pyramids and Valley of the Kings exceeded all expectations.',
                    'ar' => 'من لحظة وصولنا القاهرة وحتى مغادرتنا الأقصر، اهتم فريق إيجيبت تور برو بكل التفاصيل بدقة. الجولة الخاصة في الأهرامات ووادي الملوك فاقت كل توقعاتنا.',
                ],
                'is_verified' => true,
                'is_featured' => true,
                'is_active' => true,
                'source' => 'tripadvisor',
                'source_url' => 'https://www.tripadvisor.com',
                'published_at' => now()->subDays(5),
                'sort_order' => 2,
            ],
            [
                'customer_name' => 'Elena Rossi',
                'customer_initials' => 'ER',
                'rating' => 5,
                'content' => [
                    'en' => 'Exceptional service and unforgettable memories! The Dahabiya cruise was intimate, serene, and luxury-filled. A truly personalized experience that we will cherish forever.',
                    'ar' => 'خدمة استثنائية وذكريات لا تُنسى! رحلة الدهبية النيلية كانت قمة في الهدوء والخصوصية والفخامة. تجربة شخصية مميزة سنعتز بها دائماً.',
                ],
                'is_verified' => true,
                'is_featured' => true,
                'is_active' => true,
                'source' => 'tripadvisor',
                'source_url' => 'https://www.tripadvisor.com',
                'published_at' => now()->subDays(9),
                'sort_order' => 3,
            ],
            [
                'customer_name' => 'Marc Dubois',
                'customer_initials' => 'MD',
                'rating' => 5,
                'content' => [
                    'en' => 'Magical vacation in Egypt. Our guide was extraordinarily knowledgeable and our transfers were always on time in clean, modern vehicles. Truly a 5-star experience throughout.',
                    'ar' => 'رحلة ساحرة في مصر. المرشد كان على دراية واسعة بالتاريخ والانتقالات دائماً في الموعد بسيارات حديثة ومريحة. تجربة 5 نجوم متكاملة بكل المقاييس.',
                ],
                'is_verified' => true,
                'is_featured' => true,
                'is_active' => true,
                'source' => 'tripadvisor',
                'source_url' => 'https://www.tripadvisor.com',
                'published_at' => now()->subDays(14),
                'sort_order' => 4,
            ],
            [
                'customer_name' => 'Alexander Schmidt',
                'customer_initials' => 'AS',
                'rating' => 5,
                'content' => [
                    'en' => 'Top-notch travel company in Egypt! Excellent private drivers, 5-star cruise experience, and 24/7 responsive customer support. Will definitely book again.',
                    'ar' => 'شركة سياحية من الطراز الأول في مصر! سائقون خاصون ممتازون، تجربة كروز 5 نجوم راقية، ودعم متواصل على مدار 24 ساعة. سنكرر الحجز بالتأكيد.',
                ],
                'is_verified' => true,
                'is_featured' => true,
                'is_active' => true,
                'source' => 'tripadvisor',
                'source_url' => 'https://www.tripadvisor.com',
                'published_at' => now()->subDays(20),
                'sort_order' => 5,
            ],
            [
                'customer_name' => 'Ahmed Al-Mansoor',
                'customer_initials' => 'AM',
                'rating' => 5,
                'content' => [
                    'en' => 'Exceptional luxury journey curated with great care. Beautiful accommodations, professional VIP transfers, and a truly majestic Nile Cruise. Five stars all the way!',
                    'ar' => 'رحلة فاخرة استثنائية تم إعدادها بعناية فائقة. إقامة مميزة وانتقالات VIP راقية ونايل كروز في غاية الروعة والفخامة. خمس نجوم بكل جدارة!',
                ],
                'is_verified' => true,
                'is_featured' => true,
                'is_active' => true,
                'source' => 'tripadvisor',
                'source_url' => 'https://www.tripadvisor.com',
                'published_at' => now()->subDays(25),
                'sort_order' => 6,
            ],
        ];

        foreach ($reviews as $review) {
            Testimonial::updateOrCreate(
                ['customer_name' => $review['customer_name']],
                $review
            );
        }
    }
}
